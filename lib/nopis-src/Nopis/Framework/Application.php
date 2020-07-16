<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework;

use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Http\ResponseInterface;
use Nopis\Lib\Http\ApplicationInterface;
use Nopis\Lib\Http\NotFoundResponseException;
use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\DI\Container;
use Nopis\Lib\Event\EventManager;
use Nopis\Lib\Routing\RouteEvent;
use Nopis\Lib\Routing\RouteRegistryInterface;
use Nopis\Lib\Routing\RouteNotFoundException;
use Nopis\Lib\Security\User\UserCredentials;
use Nopis\Framework\Interceptor\InterceptorRegistry;
use Nopis\Framework\Inspector\ExceptionInspector;
use Nopis\Framework\Interceptor\InterceptorException;

/**
 * @author wangbin
 */
class Application implements ApplicationInterface
{

    /**
     * @var \Nopis\Lib\Http\RequestInterface
     */
    protected $request;

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    protected $configurator;

    /**
     * @var \Nopis\Lib\Routing\RouteRegistryInterface
     */
    protected $routeRegistry;

    /**
     * @var \Nopis\Lib\DI\ContainerInterface
     */
    protected $container;

    /**
     * @var \Nopis\Lib\Event\EventManager
     */
    protected $eventManager;

    /**
     * @var \Nopis\Lib\Security\User\UserCredentials
     */
    protected $userCredentials;

    /**
     * @var \Nopis\Framework\Module\Module[]
     */
    protected $modules = [];

    /**
     * Application's constructor function
     *
     * @param RequestInterface $request
     * @param ConfiguratorInterface $configurator
     */
    public function __construct(RequestInterface $request, ConfiguratorInterface $configurator, RouteRegistryInterface $routeRegistry)
    {
        $this->request       = $request;
        $this->configurator  = $configurator;
        $this->routeRegistry = $routeRegistry;

        define('IS_DEBUG', $this->configurator->debugIsOpen());
        error_reporting(IS_DEBUG ? E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED : 0);

        date_default_timezone_set($this->configurator->getConfig('framework.timezone.timezone_identifier'));
        define('SYS_TIMESTAMP', time());
        define('SYS_DATETIME',  date($this->configurator->getConfig('framework.timezone.datetime_format') ?: 'Y-m-d H:i:s', SYS_TIMESTAMP));
        define('SYS_DATE',      date($this->configurator->getConfig('framework.timezone.date_format') ?: 'Y-m-d', SYS_TIMESTAMP));
        define('SYS_TIMESTAMP_MONTH_START',     strtotime(date('Y-m') . '-01 00:00:01'));
        define('SYS_TIMESTAMP_WEEK_START',      strtotime(date('Y-m-d 00:00:01', strtotime('-' . (date('N') - 1) . ' day'))));
        define('SYS_TIMESTAMP_YESTODAY_START',  strtotime(date('Y-m-d 00:00:01', strtotime('-1 day'))));
        define('SYS_TIMESTAMP_DAY_START',       strtotime(date('Y-m-d') . ' 00:00:01'));
        define('SYS_TIMESTAMP_TOMORROW_START',  strtotime(date('Y-m-d 00:00:01', strtotime('+1 day'))));

        $this->boot();
    }

    /**
     * Boot app
     *
     * @return \Nopis\Framework\Application
     */
    private function boot()
    {
        $this->container = Container::getInstance($this->configurator);
        $this->eventManager = EventManager::getInstance($this->container);

        $mods = array_keys($this->routeRegistry->getRoutesRegistry());
        foreach ($mods as $mod) {
            $classname = str_replace('_', '\\', $mod) . '\\' . str_replace('_', '', $mod);
            $this->modules[$mod] = new $classname($this->request, $this->configurator, $this->container, $this->eventManager);
            $this->modules[$mod]->boot();
        }

        $this->request->setApp($this);

        return $this;
    }

    /**
     * @return \Nopis\Lib\Http\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Nopis\Lib\Http\ResponseInterface
     */
    public function getResponse()
    {
        $interceptorExecutor = (new InterceptorRegistry($this->configurator))->getExecutor();
        try {
            $this->userCredentials = $this->resovleUserCredentials();

            $routeEvent = $this->resolveRoutes($this->routeRegistry->getRoutesRegistry());
            $controllerResolver = $this->resolveController($routeEvent);
            $controller = $controllerResolver->getController();
            $interceptorResponse = $interceptorExecutor->invokeBeforeHandler($this->request, $routeEvent->getRouter(), $controller);
            if (!$interceptorResponse) {
                throw new InterceptorException(sprintf('Process hold by interceptor "%s"', get_class($interceptorExecutor->getCurrentExecutingInterceptor())));
            }

            if ($controllerResolver->hasHookResponse()) {
                return $controllerResolver->getHookResponse();
            }

            $parameters = $this->resolveParameters($controller[0], $controller[1])->getParameters();
            $method = $controller[1];
            $response = $controller[0]->$method(...$parameters);
            if (!$response instanceof ResponseInterface) {
                $msg = sprintf('the controller must return a response object (%s given).', gettype($response));
                if (null == $response) {
                    $msg .= ' did you forget to add a return statement somewhere in your controller?';
                }
                throw new NotFoundResponseException($msg);
            }
        } catch (\Exception $exception) {
            $response = (new ExceptionInspector())->handleException($exception, $this->request, $this->configurator);
        } finally {
            $interceptorExecutor->invokeAfterHandler($this->request, isset($exception) ? $exception : null);
        }
        return $response;
    }

    /**
     * Parse the modules routes and Return RouteEvent instance.
     *
     * @param array $modRouteCfg
     * @return \Nopis\Lib\Routing\RouteEvent
     */
    protected function resolveRoutes(array $modRouteCfg)
    {
        foreach ($modRouteCfg as $modName => & $modRoute) {
            $modRoute['modPath'] = $this->modules[$modName]->getPath();
            $modRoute['modName'] = $this->modules[$modName]->getName();
            $modRoute['modNamespace'] = $this->modules[$modName]->getNamespace();
        }
        $routeEvent = new RouteEvent($this, $this->request);
        $this->eventManager->broadcast($routeEvent, [$modRouteCfg, $this->configurator]);

        return $routeEvent;
    }

    /**
     * Instance the controller and return ControllerResolveEvent instance.
     *
     * @param \Nopis\Lib\Routing\RouteEvent $routeEvent
     * @return \Nopis\Framework\Controller\ControllerResolver
     */
    protected function resolveController(RouteEvent $routeEvent)
    {
        try {
            return new Controller\ControllerResolver(
                $routeEvent->getController(),
                $this->container,
                $this->request,
                $this->configurator,
                $this->userCredentials,
                $routeEvent->getRouter(),
                $this->eventManager
            );
        } catch (RouteNotFoundException $routeNotFound) {
            throw new RouteNotFoundException('not found the path resource, error: ' . $routeNotFound->getMessage());
        }
    }

    /**
     * @param \Nopis\Framework\Controller\Controller $controller
     * @param string $method
     * @return \Nopis\Framework\Controller\ParametersResolver
     */
    protected function resolveParameters(Controller\Controller $controller, string $method)
    {
        return new Controller\ParametersResolver($controller, $method);
    }

    /**
     * @return \Nopis\Lib\Security\User\UserCredentials
     */
    protected function resovleUserCredentials()
    {
        $login = $password = $extra = '';
        if ($this->request->isPost()) {
            $login    = $this->request->getPost($this->configurator->getConfig('framework.security.form_login.login_field'), '');
            $password = $this->request->getPost($this->configurator->getConfig('framework.security.form_login.password_field'), '');
            $extra    = $this->request->getPost($this->configurator->getConfig('framework.security.form_login.extra_field'), '');
        }

        $token = $this->request->getRequest($this->configurator->getConfig('framework.security.api_login_token_name') ?: '_token_', '');
        return new UserCredentials($login, $password, $token, $extra);
    }
}

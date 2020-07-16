<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Controller;

use Nopis\Lib\DI\ContainerInterface;
use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\Security\User\UserCredentials;
use Nopis\Lib\Routing\RouterInterface;
use Nopis\Lib\Event\EventManagerInterface;
use Nopis\Lib\Http\Response;
use Nopis\Lib\Latte\Engine as LatteEngine;
use Nopis\Lib\Database\DB;
use Nopis\Lib\Redis\PhpRedis;
use Nopis\Framework\Event\Template\Engine as TemplateEngine;
use Nopis\Framework\Event\Template\TemplateEngineInvokeEvent;
use Nopis\Framework\Service\ServiceDelegate;

/**
 * @author wangbin
 */
abstract class Controller
{
    /**
     * @var \Nopis\Lib\DI\ContainerInterface
     */
    private $container;

    /**
     * @var \Nopis\Lib\Http\RequestInterface
     */
    public $request;

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    private $configurator;

    /**
     * @var \Nopis\Lib\Security\User\UserCredentials
     */
    private $userCredentials;

    /**
     * @var \Nopis\Lib\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Nopis\Lib\Event\EventManagerInterface
     */
    private $eventManager;

    /**
     * @var \Nopis\Lib\Http\ResponseInterface
     */
    public $response;

    /**
     * @var \Nopis\Lib\Http\ResponseInterface
     */
    private $hookResponse;

    /**
     * @var \Nopis\Framework\Event\Template\EngineEventInterface
     */
    private $templateEngine;

    /**
     * @var array
     */
    private $viewShare;

    /**
     * @var \Nopis\Lib\Database\DBInterface
     */
    private $db;

    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var \Service\ServiceDelegateInterface
     */
    protected $service;

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\DI\ContainerInterface $container
     * @param \Nopis\Lib\Http\RequestInterface $request
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @param \Nopis\Lib\Security\User\UserCredentials $userCredentials
     * @param \Nopis\Lib\Routing\RouterInterface $router
     * @param \Nopis\Lib\Event\EventManagerInterface $eventManager
     */
    public function __construct
    (
        ContainerInterface $container,
        RequestInterface $request,
        ConfiguratorInterface $configurator,
        UserCredentials $userCredentials,
        RouterInterface $router,
        EventManagerInterface $eventManager
    )
    {
        $this->container       = $container;
        $this->request         = $request;
        $this->configurator    = $configurator;
        $this->userCredentials = $userCredentials;
        $this->router          = $router;
        $this->eventManager    = $eventManager;

        $this->response = new Response();
        $this->response->setCharset($this->configurator->getConfig('framework.charset') ?: 'utf-8');
        $this->response->setCompressible(IS_DEBUG ? false : $this->configurator->getConfig('framework.enableCompression'));
        $this->db = DB::getInstance($this->configurator->getConfig('database'));
        $this->service = new ServiceDelegate($this);

        $this->container->set('nopis.framework.service', $this->service, true);
        $this->container->set('nopis.framework.controller', $this, true);
    }

    /**
     *
     * @return \Nopis\Lib\Database\DBInterface
     */
    public function DB()
    {
        return $this->db;
    }

    /**
     * Render the View, and return the Response Object
     *
     * @param string   $view    the template view
     * @param array    $params  the parameters into the view
     * @return \Nopis\Lib\Http\Response
     */
    final public function render($view, array $params = [])
    {
        $this->viewShare && $params = array_merge($this->viewShare, $params);
        $cacheDir = $this->configurator->getRootDir() . '/pub/cache/html';

        return $this->response->setContent($this->templateEngine()->render($view, $params, $cacheDir));
    }

    /**
     * Render a json string content for ajax request
     *
     * @param array $params
     * @return \Nopis\Lib\Http\JsonResponse
     */
    final public function renderJson(array $params)
    {
        $this->viewShare && $params = array_merge($this->viewShare, $params);
        $this->response->getHeaders()->setContentType('application/json');
        if (false === ($content = json_encode($params, JSON_UNESCAPED_UNICODE))) {
            throw new EncodeJsonException($params);
        }
        $this->response->setContent($content);
        return $this->response;
    }

    /**
     * To share in some views.
     *
     * @param string $key
     * @param mixed $value
     */
    final public function viewShare($key, $value)
    {
        $this->viewShare[$key] = $value;
    }

    /**
     * Return the template engine
     *
     * @return \Nopis\Framework\Event\Template\EngineInterface
     */
    final private function templateEngine()
    {
        if (!$this->templateEngine) {
            $this->templateEngine = new TemplateEngine(new LatteEngine, $this->router, $this->configurator);
            $this->eventManager->broadcast(new TemplateEngineInvokeEvent(
                $this, $this->templateEngine, $this->router, $this->configurator, $this->container
            ));
        }

        return $this->templateEngine;
    }

    /**
     * Return a cookie in HTTP cookie
     *
     * @param string $name
     * @return string
     */
    final public function getCookie($name)
    {
        return $this->request->getCookie($name, NULL);
    }

    /**
     * Returns an array with all cookies in HTTP cookie
     *
     * @return array
     */
    final public function getCookies()
    {
        return $this->request->getCookies();
    }

    /**
     * Set a cookie to HTTP cookie
     *
     * @param string                   $name     The name of the cookie
     * @param string                   $value    The value of the cookie
     * @param int|string|\DateTime     $expire   The time the cookie expires
     * @param string                   $path     The path on the server in which the cookie will be available on
     * @param string                   $domain   The domain that the cookie is available to
     * @param bool                     $secure   Whether the cookie should only be transmitted over a secure HTTPS connection from the client
     * @param bool                     $httpOnly Whether the cookie will be made accessible only through the HTTP protocol
     */
    final public function setCookie($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true)
    {
        $cookie = $this->response->getHeaders()->newCookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        $this->response->getHeaders()->setCookie($cookie);
    }

    /**
     * Removes a cookie from HTTP cookie, but does not unset it in the browser
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     */
    final public function removeCookie($name, $path = '/', $domain = null)
    {
        $this->response->getHeaders()->removeCookie($name, $path, $domain);
    }

    /**
     * Clears a cookie in the browser
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     */
    final public function clearCookie($name, $path = '/', $domain = null)
    {
        $this->response->getHeaders()->clearCookie($name, $path, $domain);
    }

    /**
     * Return the DI container
     *
     * @return \Nopis\Lib\DI\ContainerInterface
     */
    final public function getContainer() : \Nopis\Lib\DI\ContainerInterface
    {
        return $this->container;
    }

    /**
     * Return the global Configurate
     *
     * @return \Nopis\Lib\Config\ConfiguratorInterface
     */
    final public function getConfigurator() : \Nopis\Lib\Config\ConfiguratorInterface
    {
        return $this->configurator;
    }

    /**
     * Return current user, logined or anonymous
     *
     * @return \Nopis\Lib\Security\User\UserInterface
     */
    abstract public function getCurrentUser();

    /**
     * Return router.
     *
     * @return \Nopis\Lib\Routing\RouterInterface
     */
    final public function getRouter() : \Nopis\Lib\Routing\RouterInterface
    {
        return $this->router;
    }

    /**
     * Return the event manager
     *
     * @return \Nopis\Lib\Event\EventManagerInterface
     */
    final public function getEventManager() : \Nopis\Lib\Event\EventManagerInterface
    {
        return $this->eventManager;
    }

    /**
     * @return \Nopis\Lib\Security\User\UserCredentials
     */
    function getUserCredentials(): \Nopis\Lib\Security\User\UserCredentials
    {
        return $this->userCredentials;
    }

    /**
     * Return the Redis Object
     *
     * @param boolean $writeable  if true, return the writeable redis server
     * @return \Nopis\Lib\Redis\PhpRedis
     */
    final public function getRedis(bool $writeable = false)
    {
        if (null == $this->redis) {
            $this->redis = PhpRedis::getInstance($this->getConfigurator()->getConfig('redis'));
        }

        $this->redis->readOrWrite($writeable);
        return $this->redis;
    }

    /**
     * @return \Service\ServiceDelegateInterface
     */
    final public function getService()
    {
        return $this->service;
    }

    /**
     * Return the hook Response Object
     *
     * @return \Nopis\Lib\Http\ResponseInterface
     */
    final public function getHookResponse()
    {
        return $this->hookResponse;
    }

    /**
     * @return boolean
     */
    final public function hasHookResponse()
    {
        $this->hookResponse = $this->hook();

        return $this->hookResponse instanceof Response;
    }

    /**
     * execute __initController if it exists, for user can do something in initialize controller
     */
    final private function hook()
    {
        if (method_exists($this, '__initController')) {
            return $this->__initController();
        }

        return null;
    }
}
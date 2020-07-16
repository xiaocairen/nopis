<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Routing;

use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Config\ConfiguratorInterface;

/**
 * Description of RouteResolver
 *
 * @author wangbin_hn
 */
class RouteResolver
{
    /**
     * @var \Nopis\Lib\Http\RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $modRouteCfg;

    /**
     * @var \Nopis\Lib\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    private $configurator;

    /**
     * Constructor.
     *
     * @param mixed $eventSource
     * @param \Nopis\Lib\Http\RequestInterface $request
     * @param \Nopis\Lib\Security\User\UserInterface $user
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * For Event Manager to broadcast
     *
     * @param array $modRouteCfg
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @return null
     */
    public function parse(array $modRouteCfg, ConfiguratorInterface $configurator)
    {
        $this->modRouteCfg = $modRouteCfg;
        $this->configurator = $configurator;

        $routeCollection = new RouteCollection();
        foreach ($modRouteCfg as $routes) {
            $routeCollection->add(new Route($routes['prefix'], $routes['domain'], $routes['map'], $routes['modPath'], $routes['modName'], $routes['modNamespace']));
        }

        $this->setRouter(new Router($routeCollection, $this->request, $configurator));
    }

    /**
     * Return the controller class and method by router
     *
     * @return array
     */
    public function getController()
    {
        return $this->router->getController();
    }

    /**
     * Set current router.
     *
     * @param \Nopis\Lib\Routing\RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Return current router.
     *
     * @return \Nopis\Lib\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return \Nopis\Lib\Http\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getRouteConfig()
    {
        return $this->modRouteCfg;
    }

    /**
     * @return \Nopis\Lib\Config\ConfiguratorInterface
     */
    public function getConfigurator()
    {
        return $this->configurator;
    }
}

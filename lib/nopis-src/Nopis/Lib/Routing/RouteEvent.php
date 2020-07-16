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

use Nopis\Lib\Event\Event;
use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Config\ConfiguratorInterface;

/**
 * @author Wangbin
 */
class RouteEvent extends Event
{
    /**
     * @var \Nopis\Lib\Http\RequestInterface
     */
    private $request;

    /**
     * @var \Nopis\Lib\Routing\RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param mixed $eventSource
     * @param \Nopis\Lib\Http\RequestInterface $request
     */
    public function __construct($eventSource, RequestInterface $request)
    {
        parent::__construct($eventSource);
        $this->request = $request;
    }

    /**
     * For Event Manager to broadcast
     *
     * @param array $modRouteCfg
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @return null
     */
    public function handle(array $modRouteCfg, ConfiguratorInterface $configurator)
    {
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
}

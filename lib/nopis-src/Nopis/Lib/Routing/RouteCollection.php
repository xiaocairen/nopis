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

/**
 * @author Wangbin
 */
class RouteCollection implements RouteCollectionInterface, \Countable, \IteratorAggregate
{
    /**
     * An array of the routes list
     *
     * @var \Nopis\Lib\Routing\Route[]
     */
    protected $routes = [];

    /**
     * Add a route to route collection
     *
     * @param \Nopis\Lib\Routing\RouteInterface $route
     */
    public function add(RouteInterface $route)
    {
        $this->routes[] = $route;
    }

    /**
     * Get all route
     *
     * @return \Nopis\Lib\Routing\Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Gets the current RouteCollection as an Iterator that includes all routes.
     *
     * It implements \IteratorAggregate.
     *
     * @return \ArrayIterator An \ArrayIterator object for iterating over routes
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->routes);
    }

    /**
     * Gets the number of Routes in this collection.
     *
     * @return int The number of routes
     */
    public function count()
    {
        return count($this->routes);
    }
}

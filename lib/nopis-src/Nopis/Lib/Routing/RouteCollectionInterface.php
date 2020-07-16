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
interface RouteCollectionInterface
{
    /**
     * Add a route to route collection
     *
     * @param \Nopis\Lib\Routing\RouteInterface $route
     */
    public function add(RouteInterface $route);

    /**
     * Get all route
     *
     * @return \Nopis\Lib\Routing\RouteInterface[]
     */
    public function getRoutes();
}

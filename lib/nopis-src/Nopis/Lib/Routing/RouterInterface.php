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
 *
 * @author wangbin
 */
interface RouterInterface
{
    /**
     * Return the routing controller
     *
     * @return array
     */
    public function getController();

    /**
     * Generate a url by given $action and $params
     *
     * @param type $action
     * @param array $params
     * @return string
     */
    public function generateUrl($action, array $params = []);

    /**
     * Return RouteCollection
     *
     * @return \Nopis\Lib\Routing\RouteCollectionInterface
     */
    public function getRouteCollection();

    /**
     * Return current route
     *
     * @return \Nopis\Lib\Routing\Route
     */
    public function getCurRoute();
}

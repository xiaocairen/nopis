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
interface RouteInterface
{
    /**
     * Save current request url path in current route
     *
     * @param type $curPath
     */
    public function setCurPath($curPath);

    /**
     * Return current request url path
     *
     * @return string
     */
    public function getCurPath();

    /**
     * Return the action of current request url in routes map
     *
     * @return string current request action
     */
    public function getCurAction($method);

    /**
     * Return the map url path => controller
     *
     * @return array
     */
    public function getPaths();

    /**
     * Return path of the module which route configuration in
     *
     * @return string
     */
    public function getModPath();

    /**
     * Return the name of the module which route configuration in
     *
     * @return string
     */
    public function getModName();

    /**
     * Return the namespace of the module which route configuration in
     *
     * @return string
     */
    public function getModNamespace();
}

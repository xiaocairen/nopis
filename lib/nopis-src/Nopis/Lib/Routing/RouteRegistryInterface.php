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
 * @author wb
 */
interface RouteRegistryInterface
{
    const GET  = 'get';
    const POST = 'post';
    const ANY  = 'any';

    /**
     * 注册模块的路由表
     *
     * @param string $modname       模块名，由模块的命名空间组成，反斜线以下划线代替
     * @param string $subdomain     模块配置的二级域名，留空则默认为www
     * @param string $prefix        模块路由的url前缀
     * @param array  $actionmap     一个模块的所有路由映射，即url与控制器映射
     * @return \Nopis\Framework\RouteRegistry
     */
    public function register($modname, $subdomain, $prefix, $actionmap);

    /**
     * 获取所有注册的路由表
     *
     * @return array
     */
    public function getRoutesRegistry();
}

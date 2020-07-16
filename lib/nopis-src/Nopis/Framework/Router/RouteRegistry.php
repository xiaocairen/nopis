<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Router;

use Nopis\Lib\Routing\RouteRegistryInterface;

/**
 * Description of Router
 *
 * @author wb
 */
class RouteRegistry implements RouteRegistryInterface
{

    /**
     * @var array
     */
    private $routes;

    /**
     * 注册模块的路由表
     *
     * @param string $modname       模块名，由模块的命名空间组成，反斜线以下划线代替
     * @param string $subdomain     模块配置的二级域名，留空则默认为www
     * @param string $prefix        模块路由的url前缀<br/>
     *                              前缀必须以斜线开始，例如：/member
     * @param array  $actionmap     一个模块的所有路由映射，即url与控制器映射
     * @return \Nopis\Framework\RouteRegistry
     */
    public function register($modname, $subdomain, $prefix, $actionmap)
    {
        $this->routes[$modname]['domain'] = $subdomain ?: 'www';
        $this->routes[$modname]['prefix'] = empty($prefix) ? '' : ('/' == $prefix[0] ? $prefix : '/' . $prefix);
        $this->routes[$modname]['map'] = $this->actionMap($actionmap, $modname);

        return $this;
    }

    /**
     * 获取所有注册的路由表
     *
     * @return array
     */
    public function getRoutesRegistry()
    {
        return $this->routes;
    }

    /**
     * 添加响应GET请求路由
     *
     * @param string $path   like: '/url'
     * @param string $action like: 'controllerClassName:actionName'
     * @return array
     */
    public function get($path, $action)
    {
        return [$path, $action, self::GET];
    }

    /**
     * 添加响应POST请求路由
     *
     * @param string $path      like: '/url'
     * @param string $action    like: 'controllerClassName:actionName'
     * @return array
     */
    public function post($path, $action)
    {
        return [$path, $action, self::POST];
    }

    /**
     * 添加响应GET和POST请求路由
     *
     * @param string $path      like: '/url'
     * @param string $action    like: 'controllerClassName:actionName'
     * @return array
     */
    public function any($path, $action)
    {
        return [$path, $action, self::ANY];
    }

    /**
     *
     * @param array $actionmap
     * @param string $modname
     * @return array
     */
    protected function actionMap($actionmap, $modname)
    {
        $map = [];
        $modname = str_replace('_', '', $modname);
        foreach ($actionmap as $key => $row) {
            $map[$key]['path']       = $row[0];
            $map[$key]['controller'] = $modname . ':' . $row[1];
            $map[$key]['method']     = $row[2];
        }

        return $map;
    }
}

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
 * Description of Route
 *
 * @author Wangbin
 */
class Route implements RouteInterface
{
    /**
     * @var string
     */
    protected $curPath;

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @var array
     */
    protected $legalMethod = ['get', 'post', 'any'];

    /**
     * @var string
     */
    protected $domain = 'www';

    /**
     * @var string
     */
    protected $prefix = '/';

    /**
     * @var array
     */
    protected $map;

    /**
     * @var string
     */
    protected $modPath;

    /**
     * @var string
     */
    protected $modName;

    /**
     * @var string
     */
    protected $modNamespace;

    /**
     *
     * @param string $prefix
     * @param array  $map
     * @param string $modPath
     * @param string $modName
     * @param string $modNamespace
     * @throws \RouteException
     */
    public function __construct($prefix, $domain, array $map, $modPath, $modName, $modNamespace)
    {
        $this->setPrefix($prefix);
        $this->setDomain($domain);
        $this->setMap($map);
        $this->setModPath($modPath);
        $this->setModName($modName);
        $this->setModNamespace($modNamespace);

        if (empty($this->map)) {
            throw new \RouteException(
                sprintf(
                    'The routes of Module \'%s\' cannot be empty',
                    str_replace('\\', '_', $this->modNamespace)
                )
            );
        }

        $this->parseMap();
    }

    private function parseMap()
    {
        foreach ($this->map as $key => $action) {
            $path = str_replace('//', '/', $this->prefix . rtrim(trim($action['path']), '/'));
            if (empty($path)) {
                throw new RouteException(
                    sprintf(
                        'Path cannot be empty, at module \'%s\' key \'%s\'',
                        str_replace('\\', '_', $this->modNamespace),
                        $key
                    )
                );
            }

            $controller = trim($action['controller']);
            if (empty($controller)) {
                throw new RouteException(
                    sprintf(
                        'Controller cannot be empty, at module \'%s\' key \'%s\'',
                        str_replace('\\', '_', $this->modNamespace),
                        $key
                    )
                );
            }

            $method = trim($action['method']);
            if (empty($method)) {
                throw new RouteException(
                    sprintf(
                        'Request method cannot be empty, at module \'%s\' key \'%s\'',
                        str_replace('\\', '_', $this->modNamespace),
                        $key
                    )
                );
            }
            if (!in_array($method, $this->legalMethod)) {
                throw new RouteException(
                    sprintf(
                        'Request method "%s" is illegal, at module \'%s\' key \'%s\'',
                        $method,
                        str_replace('\\', '_', $this->modNamespace),
                        $key
                    )
                );
            }

            $this->setMethods($path, $method);
            $this->setPaths($path, $controller, $method);
        }
    }

    /**
     * set model urls map
     *
     * @param string $path
     * @param string $action
     * @param string $method
     */
    protected function setPaths($path, $action, $method)
    {
        $this->paths[$path][$method] = $action;
    }

    /**
     * Save current request url path in current route
     *
     * @param type $curPath
     */
    public function setCurPath($curPath)
    {
        $this->curPath = (string) $curPath;
    }

    /**
     * Return current request url path
     *
     * @return string
     */
    public function getCurPath()
    {
        return $this->curPath;
    }

    /**
     * Return the action of current request url in routes map
     *
     * @return string current request action
     */
    public function getCurAction($method)
    {
        $method = strtolower($method);
        if (!in_array($method, $this->legalMethod)) {
            return null;
        }

        return isset($this->paths[$this->curPath][$method]) ? $this->paths[$this->curPath][$method] : (isset($this->paths[$this->curPath]['any']) ? $this->paths[$this->curPath]['any'] : null);
    }

    /**
     * Return the map url path => controller
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     *
     * @param string $path
     * @param string $method
     */
    protected function setMethods($path, $method)
    {
        $this->methods[$path][] = $method;
    }

    /**
     * return the request method of url
     *
     * @param string $path
     * @return array
     */
    public function getMethods($path)
    {
        return isset($this->methods[$path]) ? $this->methods[$path] : null;
    }

    /**
     *
     * @param string $domain
     */
    protected function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     *
     * @param string $prefix
     */
    protected function setPrefix($prefix)
    {
        $this->prefix = $prefix ?: '/';
    }

    /**
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     *
     * @param array $map
     */
    protected function setMap($map)
    {
        $this->map = $map;
    }

    /**
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * set the module path
     *
     * @param string $modPath
     */
    protected function setModPath($modPath)
    {
        $this->modPath = trim($modPath);
    }

    /**
     * Return path of the module which route configuration in
     *
     * @return string
     */
    public function getModPath()
    {
        return $this->modPath;
    }

    /**
     *
     * @param string $modName
     */
    protected function setModName($modName)
    {
        $this->modName = trim($modName);
    }

    /**
     * Return the name of the module which route configuration in
     *
     * @return string
     */
    public function getModName()
    {
        return $this->modName;
    }

    /**
     * set module namespace
     *
     * @param string $modNamespace
     */
    protected function setModNamespace($modNamespace)
    {
        $this->modNamespace = trim($modNamespace);
    }

    /**
     * Return the namespace of the module which route configuration in
     *
     * @return string
     */
    public function getModNamespace()
    {
        return $this->modNamespace;
    }
}

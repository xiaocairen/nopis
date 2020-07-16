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
 * @author Wangbin
 */
class Router implements RouterInterface
{
    /**
	 * The route collection instance.
	 *
	 * @var \Nopis\Lib\Routing\RouteCollection
	 */
	protected $routeCollection;

    /**
     * @var \Nopis\Lib\Routing\Route
     */
    protected $curRoute;

    /**
     * The Request instance.
     *
     * @var \Nopis\Lib\Http\RequestInterface
     */
    protected $request;

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    private $configurator;

    /**
     * @var array
     */
    private $generatedUrls;

    /**
     * @param \Nopis\Lib\Routing\RouteCollection $routeCollection
     * @param \Nopis\Lib\Http\Request $request
     */
    public function __construct(RouteCollection $routeCollection, RequestInterface $request, ConfiguratorInterface $configurator)
    {
        $this->routeCollection = $routeCollection;
        $this->request = $request;
        $this->configurator = $configurator;
    }

    /**
     * Return the routing controller
     *
     * @return array
     */
    public function getController()
    {
        $subDomain = $this->request->getUrl()->getSubDomain();
        $reqMethod = $this->request->isPost() ? RouteRegistryInterface::POST : RouteRegistryInterface::GET;
        $curPath   = rtrim($this->request->getUrl()->getPath(), '/');
        $suffix    = $this->getUrlSuffix();

        $curPath || $curPath = '/';
        $curPath = $curPath !== '/' && !empty($suffix) ? substr($curPath, 0, - strlen($suffix)) : $curPath;

        $explicit = $any = $domains = [];
        $universalRoute = null;
        foreach ($this->getRouteCollection() as $route) {
            $domains[] = $route->getDomain();
            if ('*' === $route->getDomain()) {
                $universalRoute = $route;
            }

            if ($route->getDomain() !== $subDomain || false === strpos($curPath, $route->getPrefix()))
                continue;

            $paths = $route->getPaths();
            if (isset($paths[$curPath])) {
                $methods = $route->getMethods($curPath);
                if (in_array($reqMethod, $methods))
                    $explicit[] = $route;
                elseif (in_array(RouteRegistryInterface::ANY, $methods))
                    $any[] = $route;
            }
        }

        $this->curRoute = $explicit ? array_shift($explicit) : ($any ? array_shift($any) : null);

        if (null === $this->curRoute) {
            $explicit = $any = $routes = [];
            foreach ($this->getRouteCollection() as $route) {
                if ($route->getDomain() !== $subDomain || false === strpos($curPath, $route->getPrefix()))
                    continue;

                foreach (array_keys($route->getPaths()) as $pathKey) {
                    if (0 === strpos($curPath, $pathKey . '/')) {
                        $methods = $route->getMethods($pathKey);
                        if (in_array($reqMethod, $methods)) {
                            $explicit[strlen($pathKey)] = $pathKey;
                            $routes[$pathKey] = $route;
                        } elseif (in_array(RouteRegistryInterface::ANY, $methods)) {
                            $any[strlen($pathKey)] = $pathKey;
                            $routes[$pathKey] = $route;
                        }
                    }
                }
            }

            if ($explicit || $any) {
                ksort($explicit);
                ksort($any);
                $matchedPath = $explicit ? array_pop($explicit) : array_pop($any);
                $this->request->convertUrl2Get(substr($curPath, strlen($matchedPath)));
                $curPath = $matchedPath;
                $this->curRoute = & $routes[$matchedPath];
            }
        }

        // support universal analysis domain
        if (null === $this->curRoute && !in_array($subDomain, $domains) && null !== $universalRoute) {
            $paths = $universalRoute->getPaths();
            if (isset($paths[$curPath])) {
                $this->curRoute = $universalRoute;
            } else {
                $explicit = $any = [];
                foreach (array_keys($universalRoute->getPaths()) as $pathKey) {
                    if (0 === strpos($curPath, $pathKey . '/')) {
                        $methods = $universalRoute->getMethods($pathKey);
                        if (in_array($reqMethod, $methods)) {
                            $explicit[strlen($pathKey)] = $pathKey;
                        } elseif (in_array(RouteRegistryInterface::ANY, $methods)) {
                            $any[strlen($pathKey)] = $pathKey;
                        }
                    }
                }

                if ($explicit || $any) {
                    ksort($explicit);
                    ksort($any);
                    $matchedPath = $explicit ? array_pop($explicit) : array_pop($any);
                    $this->request->convertUrl2Get(substr($curPath, strlen($matchedPath)));
                    $curPath = $matchedPath;
                    $this->curRoute = & $universalRoute;
                }
            }
        }

        if (null === $this->curRoute) {
            throw new RouteNotFoundException(
                sprintf(
                    'Unable to find the controller for path "%s". Maybe you forgot to add the matching route in your routing configuration?',
                    $this->request->getUrl()->getPath()
                )
            );
        }

        $paths = $this->curRoute->getPaths();
        $theAction = isset($paths[$curPath][$reqMethod]) ? $paths[$curPath][$reqMethod] : $paths[$curPath][RouteRegistryInterface::ANY];
        list($curMod, $controller, $action) = explode(':', $theAction);
        if (!$curMod || !$controller || !$action) {
            throw new RouteNotFoundException(
                sprintf('Unusable route controller name "%s" in "@%s/routes.php"', $theAction, $this->curRoute->getModName())
            );
        }

        $this->curRoute->setCurPath($curPath);

        $controller = '\\' . $this->curRoute->getModNamespace() . '\\Controller' . '\\' . $controller . 'Controller';
        if (!is_callable([$controller, $action])) {
            throw new RouteNotFoundException(sprintf('Controller action "%s::%s" is not callable', $controller, $action));
        }

        return [$controller, $action];
    }

    /**
     * Generate a url by given $action and $params
     *
     * @param type $action
     * @param array $params
     * @return string
     */
    public function generateUrl($action, array $params = [])
    {
        $key = md5($action . json_encode($params, JSON_UNESCAPED_UNICODE));
        if (empty($this->generatedUrls[$key])) {
            $_route = null;
            foreach ($this->getRouteCollection() as $route) {
                if (0 === strpos($action, $route->getModName())) {
                    $_route = & $route;
                    break;
                }
            }

            if (!$_route) {
                throw new RouteNotFoundException(
                    sprintf('Not found the path in all routing configuration which use controller "%s". Maybe you forgot to add the matching route in your routing configuration?', $action)
                );
            }

            $paths = [];
            foreach ($_route->getPaths() as $url => $arr) {
                foreach ($arr as $a) {
                    $paths[$a] = $url;
                }
            }

            if (!isset($paths[$action])) {
                throw new RouteNotFoundException(
                    sprintf('Not found the path in all routing configuration which use controller "%s". Maybe you forgot to add the matching route in your routing configuration?', $action)
                );
            }

            $url = $this->request->getUrl();
            $host = $_route->getDomain() != $url->getSubDomain() ? $url->getScheme() . '://' . $_route->getDomain() . '.' . $url->getTopDomain() : '';

            $this->generatedUrls[$key] = $host . $paths[$action] . ($paths[$action] !== '/' ? $this->getUrlSuffix() : '') . ($params ? '?' . http_build_query($params) : '');
        }

        return $this->generatedUrls[$key];
    }

    /**
     * Return RouteCollection
     *
     * @return \Nopis\Lib\Routing\RouteCollectionInterface
     */
    public function getRouteCollection()
    {
        return $this->routeCollection;
    }

    /**
     * Return current route
     *
     * @return \Nopis\Lib\Routing\RouteInterface
     */
    public function getCurRoute()
    {
        return $this->curRoute;
    }

    /**
     *
     * @return string
     */
    private function getUrlSuffix()
    {
        return $this->configurator->getConfig('framework.url.suffix');
    }
}

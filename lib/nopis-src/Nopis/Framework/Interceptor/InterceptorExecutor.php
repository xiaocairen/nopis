<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Interceptor;

use Exception;
use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Routing\RouterInterface;

/**
 * Description of InterceptorExecutor
 *
 * @author wb
 */
class InterceptorExecutor
{
    /**
     * @var \Nopis\Framework\Interceptor\InterceptorRegistry
     */
    private $registry;

    /**
     * @var \Nopis\Framework\Interceptor\InterceptorInterface[]
     */
    private $executionChain = [];

    /**
     * @var \Nopis\Framework\Interceptor\InterceptorInterface
     */
    private $curExecutingInterceptor;

    /**
     * @var array
     */
    private $executedInterceptorName = [];

    /**
     * Contructor.
     *
     * @param \Nopis\Framework\Interceptor\InterceptorRegistry $registry
     */
    public function __construct(InterceptorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Call before execute controller
     *
     * @param \Nopis\Lib\Http\RequestInterface $request
     * @param \Nopis\Lib\Routing\RouterInterface $router
     * @param array $controller
     * @return boolean
     */
    public function invokeBeforeHandler(RequestInterface $request, RouterInterface $router, array $controller)
    {
        $cname = explode('\\Controller\\', get_class($controller[0]));
        $chain = $this->registry->getExecutionChain($router->getCurRoute()->getModName(), $cname[1], $controller[1]);
        foreach ($chain as $interceptorClass) {
            $this->curExecutingInterceptor = $this->instanceInterceptor($interceptorClass);
            if (null === $this->curExecutingInterceptor)
                continue;

            if (false === $this->curExecutingInterceptor->beforeHandle($request, $router, $controller[0])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Call after execute controller, or has exception.
     *
     * @param \Nopis\Lib\Http\RequestInterface $request
     * @param \Exception $e
     * @return null
     */
    public function invokeAfterHandler(RequestInterface $request, Exception $e = null)
    {
        foreach ($this->executionChain as $interceptor) {
            $this->curExecutingInterceptor = $interceptor;
            $interceptor->afterHandle($request, $e);
        }
    }

    /**
     * @return \Nopis\Framework\Interceptor\InterceptorInterface
     */
    public function getCurrentExecutingInterceptor()
    {
        return $this->curExecutingInterceptor;
    }

    /**
     * @param string $interceptorClass
     * @return \Nopis\Framework\Interceptor\InterceptorInterface
     */
    private function instanceInterceptor($interceptorClass)
    {
        if (in_array($interceptorClass, $this->executedInterceptorName) || !class_exists($interceptorClass))
            return null;

        $interceptor = new $interceptorClass;
        if (!$interceptor instanceof \Nopis\Framework\Interceptor\InterceptorInterface) {
            unset($interceptor);
            return null;
        }

        $this->executedInterceptorName[] = $interceptorClass;
        $this->executionChain[] = $interceptor;
        return $interceptor;
    }
}

class InterceptorException extends Exception
{
}

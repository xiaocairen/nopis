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

use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Routing\RouterInterface;
use nPub\Core\MVC\Controller;

/**
 * InterceptorInterface
 *
 * @author wb
 */
interface InterceptorInterface
{

    /**
     * Call before execute controller
     *
     * @param RequestInterface $request
     * @param RouterInterface $router
     * @param Controller $controller
     * @return boolean
     */
    public function beforeHandle(RequestInterface $request, RouterInterface $router, Controller $controller);

    /**
     * Call after execute controller, or has exception.
     *
     * @param \Nopis\Lib\Http\RequestInterface $request
     * @param \Exception $e
     * @return null
     */
    public function afterHandle(RequestInterface $request, \Exception $e = null);
}

<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Inspector;

use Exception;
use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\Http\RequestInterface;

/**
 *
 * @author wb
 */
interface ExceptionInspectorInterface
{
    /**
     * @param Exception $e
     * @param \Nopis\Lib\Http\RequestInterface $request
     * @return \Nopis\Lib\Http\Response
     */
    public function handleException(Exception $e, RequestInterface $request, ConfiguratorInterface $configurator);
}

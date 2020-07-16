<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace xUtil;

use Nopis\Lib\Routing\RouteNotFoundException;
use Nopis\Framework\Controller\EncodeJsonException;
use Nopis\Framework\Exception\GlobalHandler;

/**
 * Description of GlobalExceptionHandler
 *
 * @author wangbin
 */
class GlobalExceptionHandler extends GlobalHandler
{
    public function routeNotFoundExceptionHandler(RouteNotFoundException $e)
    {
        return '没有找到此url地址';
    }

    public function encodeJsonExceptionHandler(EncodeJsonException $e)
    {
        return '生成json字符串出错 --> ' . $e->getMessage();
    }
}

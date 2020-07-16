<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Http;

/**
 *
 * @author wangbin
 */
interface ApplicationInterface
{

    /**
     * Return the Request Object
     *
     * @return \Nopis\Lib\Http\Request
     */
    public function getRequest();

    /**
     * Return Response Object
     *
     * @return \Nopis\Lib\Http\Response
     */
    public function getResponse();
}

<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Security;

/**
 * Description of SecurityContext
 *
 * @author wangbin
 */
class SecurityContext
{
    const AUTHENTICATION_ERROR_KEY = '_security.auth_error';
    const AUTHENTICATION_ERROR_MSG = '_security.auth_error_msg';
    const AUTHENTICATION_SUCCESS = '200';
    const AUTHENTICATION_LOGIN_ERROR = '404';
    const AUTHENTICATION_PASSWORD_ERROR = '500';
    const AUTHENTICATION_UNKNOWN_ERROR = '999';
    const AUTHENTICATION_ERRORS = [
        self::AUTHENTICATION_LOGIN_ERROR,
        self::AUTHENTICATION_PASSWORD_ERROR,
    ];
}

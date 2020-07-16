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

use Nopis\Lib\Http\RequestInterface;

/**
 * Description of AuthenticationFailure
 *
 * @author wb
 */
class AuthenticationFailure
{

    /**
     * Be called after login failure.
     *
     * @param \Exception $e
     */
    public static function triggerLoginFailure(\Exception $e, RequestInterface $request)
    {
        if (in_array($e->getCode(), SecurityContext::AUTHENTICATION_ERRORS)) {
            $request->getSession()->set(SecurityContext::AUTHENTICATION_ERROR_KEY, $e->getCode());
        } else {
            $request->getSession()->set(SecurityContext::AUTHENTICATION_ERROR_KEY, SecurityContext::AUTHENTICATION_UNKNOWN_ERROR);
            $request->getSession()->set(SecurityContext::AUTHENTICATION_ERROR_MSG, $e->getMessage());
        }

        throw $e;
    }

}

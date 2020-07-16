<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Security\User;

/**
 *
 * @author wangbin
 */
interface UserStoragerInterface
{

    /**
     * Return a stdClass object with $userCredentials->login and $userCredentials->credentials <br />
     *   or   False when has nobody be storaged in location
     *
     * @return stdClass|false  $userCredentials->login and $userCredentials->credentials
     */
    public function getStoragedUserCredentials();

    /**
     * Save the current user's username and password in cookie. <br />
     * $expire = 0 cookie is destroyed on close browser, <br />
     * $expire = -1 Immediately destroy cookie
     *
     * @param string $login
     * @param string $credentials
     * @param int $expire  expire time, Unit of time is second
     *
     * @return boolean
     */
    public function saveCurrentUserCredentials($login, $credentials, $expire = 0);

    /**
     * Destroy the current user's username and password in cookie. <br />
     * $expire = 0 cookie is destroyed on close browser, <br />
     * $expire = -1 Immediately destroy cookie
     *
     * @return boolean
     */
    public function destroyCurrentUserCredentials();
}

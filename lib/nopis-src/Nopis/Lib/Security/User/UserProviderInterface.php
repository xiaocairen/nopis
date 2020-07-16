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
interface UserProviderInterface
{
    /**
     * Load current user, and save the current user in location
     *
     * @param \Nopis\Lib\Security\User\UserCredentials $userCredentials
     *
     * @return \Nopis\Lib\Security\User\UserInterface
     *
     * @throws \RuntimeException   with not found code 404 or password invalid code 500
     */
    public function loadUser(UserCredentials $userCredentials);

    /**
     * Loads anonymous user
     *
     * @return \Nopis\Lib\Security\User\UserInterface
     */
    public function loadAnonymousUser();
}

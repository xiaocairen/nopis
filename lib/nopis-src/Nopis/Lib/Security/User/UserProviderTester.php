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

use Nopis\Lib\Security\User\Role\Admin;
use Nopis\Lib\Security\User\Role\Member;
use Nopis\Lib\Security\User\Role\Anonymous;
use Nopis\Lib\Security\User\UserCredentials;
use Nopis\Lib\Config\ConfiguratorInterface;

/**
 * A UserProvider Demo
 *
 * @author wangbin
 */
class UserProviderTester implements UserProviderInterface
{
    /**
     * Load current user
     *
     * @param \Nopis\Lib\Security\User\UserCredentials $userCredentials
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     *
     * @return \Nopis\Lib\Security\User\UserInterface
     */
    public function loadUser(UserCredentials $userCredentials, ConfiguratorInterface $configurator)
    {
        if ($this->isAdmin($userCredentials)) {
            $role = new Admin($configurator);
        } elseif ($this->isMember($userCredentials)) {
            $role = new Member($configurator);
        } else {
            $role = new Anonymous($configurator);
        }

        return new User($role, $configurator);
    }

    private function isAdmin(UserCredentials $userCredentials)
    {
        return $userCredentials->getLogin() == 'admin' && $userCredentials->getCredentials() == '123456';
    }

    private function isMember(UserCredentials $userCredentials)
    {
        return $userCredentials->getLogin() == 'tester' && $userCredentials->getCredentials() == '123456';
    }
}

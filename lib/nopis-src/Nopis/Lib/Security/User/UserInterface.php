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
interface UserInterface
{

    /**
     * Return the role of current user
     *
     * @return \Nopis\Lib\Security\User\Role\RoleInterface
     */
    public function role();

    /**
     * Return user's credential.
     *
     * @return string
     */
    public function getPassword();

    /**
     * Check the user if be manager
     *
     * @return boolean
     */
    public function isAdmin();

    /**
     * Check the user if be Member
     *
     * @return boolean
     */
    public function isMember();

    /**
     * Check the user if be Anonymous
     *
     * @return boolean
     */
    public function isAnonymous();
}

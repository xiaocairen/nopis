<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Security\User\Role;

/**
 *
 * @author wangbin
 */
interface RoleInterface
{
    /**
     * Check the current visit module if in forbid access module list of user
     *
     * @param string $curMod
     * @return boolean
     */
    public function inForbidAccessModules($curMod);

    /**
     * Return forward link if current module is forbid to access
     *
     * @return string
     */
    public function getForwardLink();

    /**
     * Return the key of role
     *
     * @return string
     */
    public function getKey();
}

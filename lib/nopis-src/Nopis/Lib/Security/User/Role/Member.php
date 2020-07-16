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
 * Description of Member
 *
 * @author wangbin
 */
class Member extends AbstractRole
{
    /**
     * @var string
     */
    protected $key = 'member';

    public function getKey()
    {
        return $this->key;
    }
}

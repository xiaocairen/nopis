<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\User;

use nPub\SPI\Persistence\Entity\User\Group as SPIGroup;
use Nopis\Lib\Database\TableInterface;

class UserGroup extends SPIGroup implements TableInterface
{

    /**
     * Constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        parent::__construct($properties);
    }

    /**
     * 用户组id
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * 组名称
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->group_name;
    }

    /**
     * 是否禁止[0-不禁止、1-禁止]
     * 
     * @return boolean
     */
    public function isForbid()
    {
        return $this->forbid == 1;
    }
}


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

use nPub\SPI\Persistence\Entity\UpdateHelper;

/**
 * Description of GroupUpdateHelper
 *
 * @author wangbin
 */
class UserGroupUpdateHelper extends UpdateHelper
{

    /**
     * Constructor.
     *
     * @param \nPub\SPI\Persistence\User\UserGroup $group
     */
    public function __construct(UserGroup $group)
    {
        parent::__construct($group);
    }

    /**
     * Get entity object
     *
     * @return \nPub\SPI\Persistence\User\UserGroup
     */
    public function getEntity()
    {
        return $this->entity;
    }

}

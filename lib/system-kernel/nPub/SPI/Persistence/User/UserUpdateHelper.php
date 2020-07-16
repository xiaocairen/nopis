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

use nPub\SPI\Persistence\Admin\AdminGroup;
use nPub\SPI\Persistence\Entity\UpdateHelper;

/**
 * Description of UserUpdateHelper
 *
 * @author wangbin
 */
class UserUpdateHelper extends UpdateHelper
{

    /**
     * @var \nPub\SPI\Persistence\User\UserGroup[]
     */
    private $addUserGroups = [];

    /**
     * @var \nPub\SPI\Persistence\User\UserGroup[]
     */
    private $delUserGroups = [];

    /**
     * @var \nPub\SPI\Persistence\Admin\AdminGroup[]
     */
    private $addAdminGroups = [];

    /**
     * @var \nPub\SPI\Persistence\Admin\AdminGroup[]
     */
    private $delAdminGroups = [];

    /**
     * Constructor.
     *
     * @param \nPub\SPI\Persistence\User\User $user
     */
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    /**
     * add a Group to user
     *
     * @param \nPub\SPI\Persistence\User\UserGroup $group
     * @return null
     */
    public function addUserGroup(UserGroup $group)
    {
        //if (!$this->getEntity()->isMember())
            //return;

        $nGroups = array_merge($this->getEntity()->getUserGroups(), $this->addUserGroups);
        foreach ($nGroups as $ng) {
            if ($ng->getGroupId() == $group->getGroupId()) {
                return;
            }
        }
        foreach ($this->delUserGroups as $k => $dg) {
            if ($dg->getGroupId() == $group->getGroupId()) {
                unset($this->delUserGroups[$k]);
                return;
            }
        }

        $this->addUserGroups[] = $group;
    }

    /**
     * delete a group from user
     *
     * @param \nPub\SPI\Persistence\User\UserGroup $group
     * @return null
     */
    public function delUserGroup(UserGroup $group)
    {
        //if (!$this->getEntity()->isMember())
            //return;

        foreach ($this->addUserGroups as $k => $ar) {
            if ($ar->getGroupId() == $group->getGroupId()) {
                unset($this->addUserGroups[$k]);
                return;
            }
        }
        foreach ($this->getEntity()->getUserGroups() as $userGroup) {
            if ($userGroup->getGroupId() == $group->getGroupId()) {
                $this->delUserGroups[] = $group;
                return;
            }
        }
    }

    /**
     * @return \nPub\SPI\Persistence\User\UserGroup[]
     */
    public function getAddUserGroups()
    {
        return $this->addUserGroups;
    }

    /**
     * @return \nPub\SPI\Persistence\User\UserGroup[]
     */
    public function getDelUserGroups()
    {
        return $this->delUserGroups;
    }

    /**
     * add a Group to Admin
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     * @return null
     */
    public function addAdminGroup(AdminGroup $group)
    {
        if (!$this->getEntity()->isAdmin())
            return;

        $nGroups = array_merge($this->getEntity()->getAdminGroups(), $this->addAdminGroups);
        foreach ($nGroups as $ng) {
            if ($ng->getGroupId() == $group->getGroupId()) {
                return;
            }
        }
        foreach ($this->delAdminGroups as $k => $dg) {
            if ($dg->getGroupId() == $group->getGroupId()) {
                unset($this->delAdminGroups[$k]);
                return;
            }
        }

        $this->addAdminGroups[] = $group;
    }

    /**
     * delete a group from Admin
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     * @return null
     */
    public function delAdminGroup(AdminGroup $group)
    {
        if (!$this->getEntity()->isAdmin())
            return;

        foreach ($this->addAdminGroups as $k => $ar) {
            if ($ar->getGroupId() == $group->getGroupId()) {
                unset($this->addAdminGroups[$k]);
                return;
            }
        }
        foreach ($this->getEntity()->getAdminGroups() as $AdminGroup) {
            if ($AdminGroup->getGroupId() == $group->getGroupId()) {
                $this->delAdminGroups[] = $group;
                return;
            }
        }
    }

    /**
     * @return \nPub\SPI\Persistence\Admin\AdminGroup[]
     */
    public function getAddAdminGroups()
    {
        return $this->addAdminGroups;
    }

    /**
     * @return \nPub\SPI\Persistence\Admin\AdminGroup[]
     */
    public function getDelAdminGroups()
    {
        return $this->delAdminGroups;
    }

    /**
     * Get entity object
     *
     * @return \nPub\SPI\Persistence\User\User
     */
    public function getEntity()
    {
        return $this->entity;
    }

}

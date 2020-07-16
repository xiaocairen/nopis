<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\Repository;

use nPub\API\Repository\AdminGroupServiceInterface;
use nPub\API\Repository\RepositoryInterface;
use nPub\SPI\Persistence\User\User;
use nPub\SPI\Persistence\Admin\AdminGroup;
use nPub\SPI\Persistence\Admin\AdminGroupHandlerInterface;
use nPub\SPI\Persistence\Backend\BackendMap;
use nPub\SPI\Persistence\Admin\AdminGroupCreateHelper;
use nPub\SPI\Persistence\Admin\AdminGroupUpdateHelper;
use nPub\Core\Base\Exceptions\UnauthorizedException;
use nPub\Core\Base\Exceptions\InvalidArgumentValue;


/**
 * Description of AdminGroupService
 *
 * @author wangbin
 */
class AdminGroupService implements AdminGroupServiceInterface
{

    /**
     * @var \nPub\API\Repository\RepositoryInterface
     */
    private $repository;

    /**
     *
     * @var \nPub\SPI\Persistence\Admin\AdminGroupHandlerInterface
     */
    private $groupHanlder;

    /**
     * Constructor.
     *
     * @param \nPub\API\Repository\RepositoryInterface $repository
     * @param \nPub\SPI\Persistence\Admin\AdminGroupHandlerInterface $groupHanlder
     */
    public function __construct(RepositoryInterface $repository, AdminGroupHandlerInterface $groupHanlder)
    {
        $this->repository = $repository;
        $this->groupHanlder = $groupHanlder;
    }

    /**
     * Instantiate an admin group create helper class
     *
     * @return \nPub\SPI\Persistence\Admin\AdminGroupCreateHelper
     */
    public function newGroupCreateHelper()
    {
        return $this->groupHanlder->getGroupCreateHelper();
    }

    /**
     * Instantiate an admin group update helper class
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group  Need update group
     *
     * @return \nPub\SPI\Persistence\Admin\AdminGroupUpdateHelper
     */
    public function newGroupUpdateHelper(AdminGroup $group)
    {
        return $this->groupHanlder->getGroupUpdateHelper($group);
    }

    /**
     * Create a new admin group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroupCreateHelper $groupCreateHelper
     * @param \nPub\SPI\Persistence\User\User $currentUser
     *
     * @return int|boolean  return the new group id if create success
     *
     * @throws \nPub\Core\Base\Exceptions\UnverifiedException
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function createGroup(AdminGroupCreateHelper $groupCreateHelper, User $currentUser)
    {
        if (!$currentUser->inSuperGroup()) {
            throw new UnauthorizedException('Current user has no permission to create group');
        }

        return $this->groupHanlder->createGroup($groupCreateHelper);
    }

    /**
     * Update the given group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroupUpdateHelper $groupUpdateHelper
     * @param \nPub\SPI\Persistence\User\User $currentUser
     *
     * @return boolean  Return true if update success, or false when update failure
     *
     * @throws \nPub\Core\Base\Exceptions\UnauthorizedException
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateGroup(AdminGroupUpdateHelper $groupUpdateHelper, User $currentUser)
    {
        if (!$currentUser->inSuperGroup()) {
            throw new UnauthorizedException('Current user has no permission to modify group');
        }

        return $this->groupHanlder->updateGroup($groupUpdateHelper);
    }

    /**
     * This method deletes a group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     * @param \nPub\SPI\Persistence\User\User $currentUser
     *
     * @return boolean  Return true if delete success, or false when delete failure
     *
     * @throws \nPub\Core\Base\Exceptions\UnauthorizedException
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function deleteGroup(AdminGroup $group, User $currentUser)
    {
        if (!$currentUser->inSuperGroup()) {
            throw new UnauthorizedException('Current user has no permission to delete group');
        }
        if ($group->isSuperGroup()) {
            throw new UnauthorizedException('Unable to delete super group');
        }

        return $this->groupHanlder->deleteGroup($group);
    }

    /**
     * Loads a group by the given group id.
     *
     * @param int $groupId
     *
     * @return boolean|\nPub\SPI\Persistence\Admin\AdminGroup
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function load(int $groupId)
    {
        if ($groupId <= 0)
            throw new InvalidArgumentValue('group_id', $groupId);

        $group = $this->groupHanlder->load($groupId);
        return $group ?: false;
    }

    /**
     * Loads all groups
     *
     * @param array $groupIds
     *
     * @return \nPub\SPI\Persistence\Admin\AdminGroup[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAll(array $groupIds = [])
    {
        return $this->groupHanlder->loadAll($groupIds);
    }

    /**
     * Get all permissions to access BackendMap of the group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     *
     * @return \nPub\SPI\Persistence\Backend\BackendMap[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function getPermissions(AdminGroup $group)
    {
        return $this->groupHanlder->getPermissions($group);
    }

    /**
     * Get all permissions to access BackendMap of the given groups.
     *
     * @param array $groupId
     *
     * @return \nPub\SPI\Persistence\Backend\BackendMap[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function getAllPermissions(array $groupId)
    {
        return $this->groupHanlder->getAllPermissions($groupId);
    }

    /**
     * Set the permissions to access BackendMap of the group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     * @param array $mapIds BackendMap map_id
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function setPermissions(AdminGroup $group, array $mapIds)
    {
        return $this->groupHanlder->setPermissions($group, $mapIds);
    }

    /**
     * Add a backend map to group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     * @param int $mapId
     * @return boolean
     * @throws \Exception
     */
    public function addPermission(AdminGroup $group, int $mapId)
    {
        return $this->groupHanlder->addPermission($group, $mapId);
    }

    /**
     * Delete a backend map of group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     * @param int $mapId
     * @return boolean
     * @throws \Exception
     */
    public function delPermission(AdminGroup $group, int $mapId)
    {
        return $this->groupHanlder->delPermission($group, $mapId);
    }

    /**
     * Check the group whether has permission to access the BackendMap.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     * @param \nPub\SPI\Persistence\Backend\BackendMap $backendMap
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \Exception
     */
    public function hasPermission(AdminGroup $group, BackendMap $backendMap)
    {
        return $this->groupHanlder->hasPermission($group, $backendMap->getMapId());
    }
}

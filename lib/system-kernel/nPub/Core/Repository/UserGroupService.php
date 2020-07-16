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

use nPub\API\Repository\UserGroupServiceInterface;
use nPub\API\Repository\RepositoryInterface;
use nPub\SPI\Persistence\User\UserGroupHandlerInterface;
use nPub\SPI\Persistence\User\UserGroup;
use nPub\SPI\Persistence\User\UserGroupCreateHelper;
use nPub\SPI\Persistence\User\UserGroupUpdateHelper;


/**
 * Description of UserGroupService
 *
 * @author wangbin
 */
class UserGroupService implements UserGroupServiceInterface
{

    /**
     * @var \nPub\API\Repository\RepositoryInterface
     */
    private $repository;

    /**
     *
     * @var \nPub\SPI\Persistence\User\GroupHandlerInterface
     */
    private $groupHanlder;

    /**
     * Constructor.
     *
     * @param \nPub\API\Repository\RepositoryInterface $repository
     * @param \nPub\SPI\Persistence\User\GroupHandlerInterface $groupHanlder
     */
    public function __construct(RepositoryInterface $repository, UserGroupHandlerInterface $groupHanlder)
    {
        $this->repository = $repository;
        $this->groupHanlder = $groupHanlder;
    }

    /**
     * Instantiate a group create helper class
     *
     * @return \nPub\SPI\Persistence\User\UserGroupCreateHelper
     */
    public function newGroupCreateHelper()
    {
        return $this->groupHanlder->getGroupCreateHelper();
    }

    /**
     * Instantiate a group update helper class
     *
     * @param \nPub\SPI\Persistence\User\UserGroup $group  Need update group
     *
     * @return \nPub\SPI\Persistence\User\UserGroupUpdateHelper
     */
    public function newGroupUpdateHelper(UserGroup $group)
    {
        return $this->groupHanlder->getGroupUpdateHelper($group);
    }

    /**
     * Create a new group.
     *
     * @param \nPub\SPI\Persistence\User\UserGroupCreateHelper $groupCreateHelper
     *
     * @return int|boolean  return the new group id if create success
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function createGroup(UserGroupCreateHelper $groupCreateHelper)
    {
        return $this->groupHanlder->createGroup($groupCreateHelper);
    }

    /**
     * Update the given group.
     *
     * @param \nPub\SPI\Persistence\User\UserGroupUpdateHelper $groupUpdateHelper
     *
     * @return boolean  Return true if update success, or false when update failure
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateGroup(UserGroupUpdateHelper $groupUpdateHelper)
    {
        return $this->groupHanlder->updateGroup($groupUpdateHelper);
    }

    /**
     * This method deletes a group.
     *
     * @param \nPub\SPI\Persistence\User\UserGroup $group
     *
     * @return boolean  Return true if delete success, or false when delete failure
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function deleteGroup(UserGroup $group)
    {
        return $this->groupHanlder->deleteGroup($group);
    }

    /**
     * Loads a group by the given group id.
     *
     * @param int $groupId
     *
     * @return boolean|\nPub\SPI\Persistence\User\UserGroup
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function load(int $groupId)
    {
        return $this->groupHanlder->load($groupId);
    }

    /**
     * Loads all groups.
     *
     * @param array $groupIds
     *
     * @return \nPub\SPI\Persistence\User\UserGroup[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAll(array $groupIds = [])
    {
        return $this->groupHanlder->loadAll($groupIds);
    }

    /**
     * check the group if has user.
     *
     * @param int $groupId
     *
     * @return int return number of user, no user return 0
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasUser(int $groupId)
    {
        return $this->groupHanlder->hasUser($groupId);
    }
}

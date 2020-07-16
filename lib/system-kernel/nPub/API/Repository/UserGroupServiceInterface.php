<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\API\Repository;

use nPub\SPI\Persistence\User\UserGroup;
use nPub\SPI\Persistence\User\UserGroupCreateHelper;
use nPub\SPI\Persistence\User\UserGroupUpdateHelper;

/**
 *
 * @author wangbin
 */
interface UserGroupServiceInterface
{
    /**
     * Instantiate a group create helper class
     *
     * @return \nPub\SPI\Persistence\User\UserGroupCreateHelper
     */
    public function newGroupCreateHelper();

    /**
     * Instantiate a group update helper class
     *
     * @param \nPub\SPI\Persistence\User\UserGroup $group  Need update group
     *
     * @return \nPub\SPI\Persistence\User\UserGroupUpdateHelper
     */
    public function newGroupUpdateHelper(UserGroup $group);

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
    public function createGroup(UserGroupCreateHelper $groupCreateHelper);

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
    public function updateGroup(UserGroupUpdateHelper $groupUpdateHelper);

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
    public function deleteGroup(UserGroup $group);

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
    public function load(int $groupId);

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
    public function loadAll(array $groupIds = []);

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
    public function hasUser(int $groupId);
}

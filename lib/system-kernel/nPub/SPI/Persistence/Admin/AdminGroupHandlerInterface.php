<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Admin;

/**
 * @author wangbin
 */
interface AdminGroupHandlerInterface
{

    /**
     * Instantiate a group create helper class
     *
     * @return \nPub\SPI\Persistence\Admin\AdminGroupCreateHelper
     */
    public function getGroupCreateHelper();

    /**
     * Instantiate a group update helper class
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group  Need update group
     *
     * @return \nPub\SPI\Persistence\Admin\AdminGroupUpdateHelper
     */
    public function getGroupUpdateHelper(AdminGroup $group);

    /**
     * Create a new group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroupCreateHelper $groupCreateHelper
     *
     * @return int|boolean  return the new group id if create success
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function createGroup(AdminGroupCreateHelper $groupCreateHelper);

    /**
     * Update the given group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroupUpdateHelper $groupUpdateHelper
     *
     * @return boolean  Return true if update success, or false when update failure
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateGroup(AdminGroupUpdateHelper $groupUpdateHelper);

    /**
     * This method deletes a group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     *
     * @return boolean  Return true if delete success, or false when delete failure
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function deleteGroup(AdminGroup $group);

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
    public function load(int $groupId);

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
    public function loadAll(array $groupIds = []);

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
    public function getPermissions(AdminGroup $group);

    /**
     * Get all permissions to access BackendMap of the given groups.
     *
     * @param array $groupIds
     *
     * @return \nPub\SPI\Persistence\Backend\BackendMap[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function getAllPermissions(array $groupIds);

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
    public function setPermissions(AdminGroup $group, array $mapIds);

    /**
     * Add a backend map to group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     * @param int $mapId
     * @return boolean
     * @throws \Exception
     */
    public function addPermission(AdminGroup $group, int $mapId);

    /**
     * Delete a backend map of group.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     * @param int $mapId
     * @return boolean
     * @throws \Exception
     */
    public function delPermission(AdminGroup $group, int $mapId);

    /**
     * Check the group whether has permission to access the BackendMap.
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group
     * @param int $mapId BackendMap map_id
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \Exception
     */
    public function hasPermission(AdminGroup $group, int $mapId);
}

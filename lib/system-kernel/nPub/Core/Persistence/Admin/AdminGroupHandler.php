<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\Persistence\Admin;

use Nopis\Lib\Database\DBInterface;
use nPub\SPI\Persistence\Admin\AdminGroupHandlerInterface;
use nPub\SPI\Persistence\Admin\AdminGroup;
use nPub\SPI\Persistence\Admin\AdminGroupCreateHelper;
use nPub\SPI\Persistence\Admin\AdminGroupUpdateHelper;

/**
 * Description of GroupHandler
 *
 * @author wangbin
 */
class AdminGroupHandler implements AdminGroupHandlerInterface
{

    /**
     * @var \Nopis\Lib\Database\DBInterface
     */
    private $pdo;

    /**
     * groups table.
     *
     * @var string
     */
    private $adminGroup = 'admin_group';

    /**
     * backend map table.
     *
     * @var string
     */
    private $backendMap = 'backend_map';

    /**
     * the map backend to group table.
     *
     * @var string
     */
    private $adminGroupBackend = 'admin_group_backend';

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\Database\DBInterface $pdo
     */
    public function __construct(DBInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Instantiate a group create helper class
     *
     * @return \nPub\SPI\Persistence\Admin\AdminGroupCreateHelper
     */
    public function getGroupCreateHelper()
    {
        return new AdminGroupCreateHelper();
    }

    /**
     * Instantiate a group update helper class
     *
     * @param \nPub\SPI\Persistence\Admin\AdminGroup $group  Need update group
     *
     * @return \nPub\SPI\Persistence\Admin\AdminGroupUpdateHelper
     */
    public function getGroupUpdateHelper(AdminGroup $group)
    {
        return new AdminGroupUpdateHelper($group);
    }

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
    public function createGroup(AdminGroupCreateHelper $groupCreateHelper)
    {
        if (!$this->pdo->insert($this->adminGroup)->values($groupCreateHelper->getCreationFieldsValues())->exec()) {
            return false;
        }

        return $this->pdo->lastInsertId();
    }

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
    public function updateGroup(AdminGroupUpdateHelper $groupUpdateHelper)
    {
        if (false === $this->pdo->update($this->adminGroup)
                ->set($groupUpdateHelper->getUpdationFieldsValues())
                ->where('group_id', '=', $groupUpdateHelper->getEntity()->getGroupId())->exec()) {
            return false;
        }

        return true;
    }

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
    public function deleteGroup(AdminGroup $group)
    {
        $in = $this->pdo->inTransaction();
        $in || $this->pdo->beginTransaction();

        $where = ['group_id', '=', $group->getGroupId()];
        if (!$this->pdo->delete()->from($this->adminGroup)->where($where)->exec()) {
            $this->pdo->rollBack();
            return false;
        }

        if (false === $this->pdo->delete()->from($this->adminGroupBackend)->where($where)->exec()) {
            $this->pdo->rollBack();
            return false;
        }

        $in || $this->pdo->commit();
        return true;
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
        $maps = $this->pdo->select('m.*')
                ->from($this->adminGroupBackend, 'gb')
                ->join($this->backendMap, 'm', ['m.map_id', 'gb.map_id'])
                ->where('gb.group_id', '=', $groupId)
                ->query()->fetchAll('\nPub\SPI\Persistence\Backend\BackendMap');

        $argument = $maps ? ['backendMaps' => $maps] : [];

        $query = $this->pdo->select()
                ->from($this->adminGroup)
                ->where('group_id', '=', $groupId)
                ->query();

        return $query->fetch('\nPub\SPI\Persistence\Admin\AdminGroup', [$argument]);
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
        $query = $this->pdo->select()
                ->from($this->adminGroup);
        if (!empty($groupIds)) {
            $query->where(_in_('group_id', $groupIds));
        }
        $query->orderBy('group_id', 'DESC')->query();

        return $query->fetchAll('\nPub\SPI\Persistence\Admin\AdminGroup');
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
        $query = $this->pdo->select('m.*')
                ->from($this->adminGroupBackend, 'gb')
                ->join($this->backendMap, 'm', ['m.map_id', 'gb.map_id'])
                ->where('gb.group_id', '=', $group->getGroupId())
                ->query();

        return $query->fetchAll('\nPub\SPI\Persistence\Backend\BackendMap');
    }

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
    public function getAllPermissions(array $groupIds)
    {
        $query = $this->pdo->select('m.*')
                ->from($this->adminGroupBackend, 'gb')
                ->join($this->backendMap, 'm', ['m.map_id', 'gb.map_id'])
                ->where(_in_('gb.group_id', $groupIds))
                ->query();

        return $query->fetchAll('\nPub\SPI\Persistence\Backend\BackendMap');
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
        if (!$mapIds) {
            return false === $this->pdo->delete()->from($this->adminGroupBackend)->where('group_id', '=', $group->getGroupId())->exec() ? false : true;
        } else {
            $values = [];
            foreach ($mapIds as $mapId) {
                $values[] = ['group_id' => $group->getGroupId(), 'map_id' => $mapId];
            }

            $in = $this->pdo->inTransaction();
            $in || $this->pdo->beginTransaction();
            if (false === $this->pdo->insert($this->adminGroupBackend)->values($values)->onDuplicateKeyUpdate(['group_id', 'map_id'])->exec()) {
                $this->pdo->rollBack();
                return false;
            }
            if (false === $this->pdo->delete()->from($this->adminGroupBackend)->where(_and_(['group_id', '=', $group->getGroupId()], _not_in_('map_id', $mapIds)))->exec()) {
                $this->pdo->rollBack();
                return false;
            }

            $in || $this->pdo->commit();
        }
        return true;
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
        $res = $this->pdo->insert($this->adminGroupBackend)
                ->values(['group_id' => $group->getGroupId(), 'map_id' => $mapId])
                ->onDuplicateKeyUpdate(['group_id'])
                ->exec();
        if (false === $res) {
            throw new \Exception('Query error in group::addPermission');
        }

        return (boolean) $res;
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
        $res = $this->pdo->delete()->from($this->adminGroupBackend)
                ->where(_and_(['group_id', '=', $group->getGroupId()], ['map_id', '=', $mapId]))
                ->exec();
        if (false === $res) {
            throw new \Exception('Query error in group::delPermission');
        }

        return (boolean) $res;
    }

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
    public function hasPermission(AdminGroup $group, int $mapId)
    {
        $has = $this->pdo->select()->from($this->adminGroupBackend)
                ->where(_and_(['group_id', '=', $group->getGroupId()], ['map_id', '=', $mapId]))
                ->count();
        if (false === $has) {
            throw new \Exception('Query error in group::hasPermission');
        }

        return (boolean) $has;
    }
}

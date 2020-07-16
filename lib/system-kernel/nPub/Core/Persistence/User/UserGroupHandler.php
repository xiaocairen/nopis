<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\Persistence\User;

use Nopis\Lib\Database\DBInterface;
use nPub\SPI\Persistence\User\UserGroupHandlerInterface;
use nPub\SPI\Persistence\User\UserGroup;
use nPub\SPI\Persistence\User\UserGroupCreateHelper;
use nPub\SPI\Persistence\User\UserGroupUpdateHelper;

/**
 * Description of GroupHandler
 *
 * @author wangbin
 */
class UserGroupHandler implements UserGroupHandlerInterface
{

    /**
     * @var \Nopis\Lib\Database\DBInterface
     */
    private $pdo;

    /**
     * the map table of user to groups.
     *
     * @var string
     */
    private $userGroupMap = 'user_group_map';

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
     * @return \nPub\SPI\Persistence\User\UserGroupCreateHelper
     */
    public function getGroupCreateHelper()
    {
        return new UserGroupCreateHelper();
    }

    /**
     * Instantiate a group update helper class
     *
     * @param \nPub\SPI\Persistence\User\UserGroup $group  Need update group
     *
     * @return \nPub\SPI\Persistence\User\UserGroupUpdateHelper
     */
    public function getGroupUpdateHelper(UserGroup $group)
    {
        return new UserGroupUpdateHelper($group);
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
        if (!$this->pdo->insert(UserGroup::tableName())
                ->values($groupCreateHelper->getCreationFieldsValues())->exec()) {
            return false;
        }

        return $this->pdo->lastInsertId();
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
        if (false === $this->pdo->update(UserGroup::tableName())
                ->set($groupUpdateHelper->getUpdationFieldsValues())
                ->where('group_id', '=', $groupUpdateHelper->getEntity()->getGroupId())->exec()) {
            return false;
        }

        return true;
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
        $in = $this->pdo->inTransaction();
        $in || $this->pdo->beginTransaction();

        $where = ['group_id', '=', $group->getGroupId()];
        if (!$this->pdo->delete()->from(UserGroup::tableName())->where($where)->exec()) {
            $this->pdo->rollBack();
            return false;
        }

        if (false === $this->pdo->delete()->from($this->userGroupMap)->where($where)->exec()) {
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
     * @return boolean|\nPub\SPI\Persistence\User\UserGroup
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function load(int $groupId)
    {
        $query = $this->pdo->select()
                ->from(UserGroup::tableName())
                ->where('group_id', '=', $groupId)
                ->query();

        return $query->fetch('\nPub\SPI\Persistence\User\UserGroup');
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
        $query = $this->pdo->select()->from(UserGroup::tableName());
        if ($groupIds) {
            $query->where(_in_('group_id', $groupIds));
        }
        $query->orderBy('group_id', 'DESC')->query();

        return $query->fetchAll('\nPub\SPI\Persistence\User\UserGroup');
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
        return $this->pdo->select()->from($this->userGroupMap)
                ->where('group_id', '=', $groupId)
                ->count();
    }
}

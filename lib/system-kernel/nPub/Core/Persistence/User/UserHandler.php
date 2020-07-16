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
use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\Security\User\UserEncryptorInterface;
use nPub\SPI\Persistence\User\UserHandlerInterface;
use nPub\SPI\Persistence\User\User;
use nPub\SPI\Persistence\User\UserGroup;
use nPub\SPI\Persistence\User\UserCreateHelper;
use nPub\SPI\Persistence\User\UserUpdateHelper;
use nPub\SPI\Persistence\Admin\AdminGroup;
use nPub\Core\Base\Exceptions\UnusableAccountException;
use ReflectionClass;
use ReflectionProperty;

/**
 * Description of Handler
 *
 * @author wangbin
 */
class UserHandler implements UserHandlerInterface
{

    /**
     * @var \Nopis\Lib\Database\DBInterface
     */
    private $pdo;

    /**
     * the map table of user to group
     *
     * @var string
     */
    private $userGroupMap = 'user_group_map';

    /**
     * the map table of user to group
     *
     * @var string
     */
    private $adminGroupMap = 'admin_group_map';

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
     * Instantiate a user create helper class, if given $adminGroup, will create an admin user.
     *
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @param \nPub\SPI\Persistence\User\UserGroup $userGroup
     * @param nPub\SPI\Persistence\Admin\AdminGroup $adminGroup
     *
     * @return \nPub\SPI\Persistence\User\UserCreateHelper
     */
    public function getUserCreateHelper(ConfiguratorInterface $configurator, UserGroup $userGroup = null, AdminGroup $adminGroup = null)
    {
        return new UserCreateHelper($configurator, $userGroup, $adminGroup);
    }

    /**
     * Instantiate a user update helper class
     *
     * @param \nPub\SPI\Persistence\User\User $user  Need update user
     *
     * @return \nPub\SPI\Persistence\User\UserUpdateHelper
     */
    public function getUserUpdateHelper(User $user)
    {
        return new UserUpdateHelper($user);
    }

    /**
     * Create a new user.
     *
     * @param \nPub\SPI\Persistence\User\UserCreateHelper $userCreateHelper
     * @param \Nopis\Lib\Security\User\UserEncryptorInterface $userEncryptor
     *
     * @return \nPub\SPI\Persistence\User\User|boolean  Return the create user if create success, or false when create failure
     *
     * @throws \nPub\Core\Base\Exceptions\UnverifiedException
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function createUser(UserCreateHelper $userCreateHelper, UserEncryptorInterface $userEncryptor)
    {
        $user = $userCreateHelper->getEntity();

        $fields = array();
        $reflClass = new ReflectionClass($user);
        foreach ($reflClass->getProperties(ReflectionProperty::IS_PUBLIC + ReflectionProperty::IS_PROTECTED) as $property) {
            $propertyName = $property->getName();
            $propertyValue = $user->getPropertyValue($propertyName);
            $docComment = $userCreateHelper->getDocComment($propertyName);
            if (isset($docComment['primary'])) {
                if ('primary' == $docComment['primary']) {
                    continue;
                }
                elseif ('unique' == $docComment['primary']) {
                    if (empty($propertyValue))
                        throw new \Exception(sprintf("the unique field %s's value is empty", $propertyName));
                    // 检查索引是唯一的字段
                    if ($this->pdo->select('COUNT(*)')->from(User::tableName())->where([$propertyName, '=', $propertyValue])->query()->fetchColumn())
                        throw new UnusableAccountException(sprintf("The {$propertyName} '%s' is exists", $propertyValue));
                }
            }

            if (isset($docComment['encrypt']) && $docComment['encrypt'] == 'password') {
                // 加密密码字段
                $fields[$propertyName] = $userEncryptor->encrypt($propertyValue);
            }
            else {
                $fields[$propertyName] = $propertyValue;
            }
        }
        unset($fields['userGroups'], $fields['adminGroups']);

        $adminGroup = $userCreateHelper->getAdminGroup();
        $userGroup = $userCreateHelper->getUserGroup();
        if ($userGroup || $adminGroup) {  // 创建管理员账号
            $in = $this->pdo->inTransaction();
            $in || $this->pdo->beginTransaction();
            if (!$this->pdo->insert(User::tableName())->values($fields)->exec()) {
                $this->pdo->rollBack();
                return false;
            }

            $userId = $this->pdo->lastInsertId();
            if ($adminGroup && !$this->pdo->insert($this->adminGroupMap)->values([
                    'user_id' => $userId, 'group_id' => $adminGroup->getGroupId()
                ])->exec()) {
                $this->pdo->rollBack();
                return false;
            }
            if ($userGroup && !$this->pdo->insert($this->userGroupMap)->values([
                    'user_id' => $userId, 'group_id' => $userGroup->getGroupId()
                ])->exec()) {
                $this->pdo->rollBack();
                return false;
            }

            $in || $this->pdo->commit();
        } else {    // 创建普通用户
            if (!$this->pdo->insert(User::tableName())->values($fields)->exec()) {
                return false;
            }

            $userId = $this->pdo->lastInsertId();
        }

        $fields['user_id'] = $userId;
        return new User($userCreateHelper->getConfigurator(), $fields);
    }

    /**
     * Update the given user. but barring user's group
     *
     * @param \nPub\SPI\Persistence\User\UserUpdateHelper $userUpdateHelper
     *
     * @return boolean  Return true if update success, or false when update failure
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateUser(UserUpdateHelper $userUpdateHelper)
    {
        $user = $userUpdateHelper->getEntity();
        $fields = $userUpdateHelper->getUpdationFieldsValues();

        return false === $this->pdo->update(User::tableName())->set($fields)->where('user_id', '=', $user->getUserId())->exec() ? false : true;
    }

    /**
     * Update username of the given user.
     *
     * @param \nPub\SPI\Persistence\User\User $user
     * @param string $username
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateUsername(User $user, $username)
    {
        return false === $this->pdo->update(User::tableName())->set(['username' => $username])->where('user_id', '=', $user->getUserId())->exec() ? false : true;
    }

    /**
     * Update phone of the given user.
     *
     * @param \nPub\SPI\Persistence\User\User $user
     * @param string $phone
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateUserPhone(User $user, $phone)
    {
        return false === $this->pdo->update(User::tableName())->set(['phone' => $phone])->where('user_id', '=', $user->getUserId())->exec() ? false : true;
    }

    /**
     * Update password of the given user.
     *
     * @param \nPub\SPI\Persistence\User\User $user
     * @param string $password
     * @param \Nopis\Lib\Security\User\UserEncryptorInterface $userEncryptor
     *
     * @return boolean  Return true if update success, or false when update failure
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateUserPassword(User $user, $password, UserEncryptorInterface $userEncryptor)
    {
        $password = $userEncryptor->encrypt($password);
        $user_id = $user->getUserId();
        return false === $this->pdo->update(User::tableName())->set(['password' => $password])->where('user_id', '=', $user_id)->exec() ? false : true;
    }

    /**
     * Update user's group, can simultaneously add some groups and delete some groups
     *
     * @param \nPub\SPI\Persistence\User\UserUpdateHelper $userUpdateHelper
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateUserGroups(UserUpdateHelper $userUpdateHelper)
    {
        $userId = $userUpdateHelper->getEntity()->getUserId();
        $addGroups = $userUpdateHelper->getAddUserGroups();
        $delGroups = $userUpdateHelper->getDelUserGroups();

        $in = $this->pdo->inTransaction();
        $in || $this->pdo->beginTransaction();
        if (!empty($addGroups)) {
            $addList = [];
            foreach ($addGroups as $group) {
                $addList[] = ['user_id' => $userId, 'group_id' => $group->getGroupId()];
            }
            if (!$this->pdo->insert($this->userGroupMap)->values($addList)->exec()) {
                $this->pdo->rollBack();
                return false;
            }
        }

        if (!empty($delGroups)) {
            $andParams = [];
            foreach ($delGroups as $group) {
                $andParams[] = _and_(['user_id', '=', $userId], ['group_id', '=', $group->getGroupId()]);
            }
            if (!$this->pdo->delete()->from($this->userGroupMap)->where(count($andParams) > 1 ? _or_(...$andParams) : $andParams[0])->exec()) {
                $this->pdo->rollBack();
                return false;
            }
        }
        $in || $this->pdo->commit();

        return true;
    }

    /**
     * Update user's admin group, can simultaneously add some groups and delete some groups
     *
     * @param \nPub\SPI\Persistence\User\UserUpdateHelper $userUpdateHelper
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateAdminGroups(UserUpdateHelper $userUpdateHelper)
    {
        $userId = $userUpdateHelper->getEntity()->getUserId();
        $addGroups = $userUpdateHelper->getAddAdminGroups();
        $delGroups = $userUpdateHelper->getDelAdminGroups();

        $in = $this->pdo->inTransaction();
        $in || $this->pdo->beginTransaction();
        if (!empty($addGroups)) {
            $addList = [];
            foreach ($addGroups as $group) {
                $addList[] = ['user_id' => $userId, 'group_id' => $group->getGroupId()];
            }
            if (!$this->pdo->insert($this->adminGroupMap)->values($addList)->exec()) {
                $this->pdo->rollBack();
                return false;
            }
        }

        if (!empty($delGroups)) {
            $andParams = [];
            foreach ($delGroups as $group) {
                $andParams[] = _and_(['user_id', '=', $userId], ['group_id', '=', $group->getGroupId()]);
            }
            if (!$this->pdo->delete()->from($this->adminGroupMap)->where(count($andParams) > 1 ? _or_(...$andParams) : $andParams[0])->exec()) {
                $this->pdo->rollBack();
                return false;
            }
        }
        $in || $this->pdo->commit();

        return true;
    }

    /**
     * Update user's last login time
     *
     * @param \nPub\SPI\Persistence\User\User $user $user
     * @param string $time
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateUserLoginTime(User $user, $time)
    {
        return false === $this->pdo->update(User::tableName())->set(['last_login_time' => $time])->where('user_id', '=', $user->getUserId())->exec() ? false : true;
    }

    /**
     * This method deletes a user, first set the field is_del to 1, second delete this data from database.
     *
     * @param \nPub\SPI\Persistence\User\User $user
     *
     * @return boolean  Return true if delete success, or false when delete failure
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function deleteUser(User $user)
    {
        if ($user->isDel()) {
            $in = $this->pdo->inTransaction();
            $in || $this->pdo->beginTransaction();

            if (!$this->pdo->delete()->from(User::tableName())->where('user_id', '=', $user->getUserId())->exec()) {
                $this->pdo->rollBack();
                return false;
            }

            if ($user->getAdminGroups() && false === $this->pdo->delete()->from($this->adminGroupMap)->where('user_id', '=', $user->getUserId())->exec()) {
                $this->pdo->rollBack();
                return false;
            }

            if ($user->getUserGroups() && false === $this->pdo->delete()->from($this->userGroupMap)->where('user_id', '=', $user->getUserId())->exec()) {
                $this->pdo->rollBack();
                return false;
            }

            $in || $this->pdo->commit();
        } else {
            if (!$this->pdo->update(User::tableName())->set(['is_del' => 1])->where('user_id', '=', $user->getUserId())->exec()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Loads a user by the given user id.
     *
     * @param int $userId
     * @param bool $withGroup
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     *
     * @return boolean|\nPub\SPI\Persistence\User\User
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function load(int $userId, bool $withGroup, ConfiguratorInterface $configurator)
    {
        $user = $this->pdo->select()->from(User::tableName())
                ->where('user_id', '=', $userId)
                ->query()
                ->fetch('\nPub\SPI\Persistence\User\User', [$configurator]);
        if (!$user) {
            return false;
        };
        if ($withGroup) {
            $user->userGroups = $this->loadUserGroups($user->user_id);
            $user->adminGroups = $this->loadAdminGroups($user->user_id);
        }

        return $user;
    }

    /**
     * Loads a user for the given login.
     *
     * @param string $login  username
     * @param bool $withGroup
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     *
     * @return boolean|\nPub\SPI\Persistence\User\User
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadByLogin(string $login, bool $withGroup, ConfiguratorInterface $configurator)
    {
        $user = $this->pdo->select()->from(User::tableName())
                ->where('username', '=', $login)
                ->query()
                ->fetch('\nPub\SPI\Persistence\User\User', [$configurator]);
        if (!$user) {
            return false;
        }
        if ($withGroup) {
            $user->userGroups = $this->loadUserGroups($user->user_id);
            $user->adminGroups = $this->loadAdminGroups($user->user_id);
        }

        return $user;
    }

    /**
     * Loads a user for the given telephone.
     *
     * @param string $phone
     * @param bool $withGroup
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     *
     * @return boolean|\nPub\SPI\Persistence\User\User
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadByPhone(string $phone, bool $withGroup, ConfiguratorInterface $configurator)
    {
        $user = $this->pdo->select()->from(User::tableName())
                ->where('phone', '=', $phone)
                ->query()
                ->fetch('\nPub\SPI\Persistence\User\User', [$configurator]);
        if (!$user) {
            return false;
        }
        if ($withGroup) {
            $user->userGroups = $this->loadUserGroups($user->user_id);
            $user->adminGroups = $this->loadAdminGroups($user->user_id);
        }

        return $user;
    }

    /**
     * Loads a user for the given telephone.
     *
     * @param string $uuid
     * @param bool $withGroup
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     *
     * @return boolean|\nPub\SPI\Persistence\User\User
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadByUuid(string $uuid, bool $withGroup, ConfiguratorInterface $configurator)
    {
        $user = $this->pdo->select()->from(User::tableName())
                ->where('uuid', '=', $uuid)
                ->query()
                ->fetch('\nPub\SPI\Persistence\User\User', [$configurator]);
        if (!$user) {
            return false;
        }
        if ($withGroup) {
            $user->userGroups = $this->loadUserGroups($user->user_id);
            $user->adminGroups = $this->loadAdminGroups($user->user_id);
        }

        return $user;
    }

    /**
     * Loads anonymous user
     *
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     *
     * @return \nPub\SPI\Persistence\User\User
     */
    public function loadAnonymous(ConfiguratorInterface $configurator)
    {
        return new User(
            $configurator,
            array(
                'user_id' => 0,
                'username' => 'Anonymous'
            )
        );
    }

    /**
     * Loads user groups of user.
     *
     * @param int $user_id
     *
     * @return \nPub\SPI\Persistence\User\UserGroup[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadUserGroups(int $user_id)
    {
        return $this->pdo->select('g.*')
                ->from($this->userGroupMap, 'gm')
                ->join(UserGroup::tableName(), 'g', 'g.group_id=gm.group_id')
                ->where('gm.user_id', '=', $user_id)
                ->query()->fetchAll('\nPub\SPI\Persistence\User\UserGroup');
    }

    /**
     * Loads admin groups of user.
     *
     * @param int $user_id
     *
     * @return \nPub\SPI\Persistence\Admin\AdminGroup[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAdminGroups(int $user_id)
    {
        return $this->pdo->select('g.*')
                ->from($this->adminGroupMap, 'gm')
                ->join(AdminGroup::tableName(), 'g', 'g.group_id=gm.group_id')
                ->where('gm.user_id', '=', $user_id)
                ->query()->fetchAll('\nPub\SPI\Persistence\Admin\AdminGroup');
    }

    /**
     * Check if the user login does not exist
     *
     * @param string $login
     * @return int
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasLogin(string $login)
    {
        return $this->pdo->select()->from(User::tableName())->where('username', '=', $login)->count();
    }

    /**
     * Check if the user phone does not exist
     *
     * @param string $phone
     * @return int
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasPhone(string $phone)
    {
        return $this->pdo->select()->from(User::tableName())->where('phone', '=', $phone)->count();
    }

}

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

use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\Security\User\UserEncryptorInterface;
use nPub\SPI\Persistence\Admin\AdminGroup;

/**
 * @author wangbin
 */
interface UserHandlerInterface
{
    /**
     * Instantiate a user create helper class, if given $adminGroup, will create an admin user.
     *
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @param \nPub\SPI\Persistence\User\UserGroup $userGroup
     * @param nPub\SPI\Persistence\Admin\AdminGroup $adminGroup
     *
     * @return \nPub\SPI\Persistence\User\UserCreateHelper
     */
    public function getUserCreateHelper(ConfiguratorInterface $configurator, UserGroup $userGroup = null, AdminGroup $adminGroup = null);

    /**
     * Instantiate a user update helper class
     *
     * @param \nPub\SPI\Persistence\User\User $user  Need update user
     *
     * @return \nPub\SPI\Persistence\User\UserUpdateHelper
     */
    public function getUserUpdateHelper(User $user);

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
    public function createUser(UserCreateHelper $userCreateHelper, UserEncryptorInterface $userEncryptor);

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
    public function updateUser(UserUpdateHelper $userUpdateHelper);

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
    public function updateUsername(User $user, $username);

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
    public function updateUserPhone(User $user, $phone);

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
    public function updateUserPassword(User $user, $password, UserEncryptorInterface $userEncryptor);

    /**
     * Update user's group, can add some groups and delete some groups simultaneously
     *
     * @param \nPub\SPI\Persistence\User\UserUpdateHelper $userUpdateHelper
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateUserGroups(UserUpdateHelper $userUpdateHelper);

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
    public function updateAdminGroups(UserUpdateHelper $userUpdateHelper);

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
    public function updateUserLoginTime(User $user, $time);

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
    public function deleteUser(User $user);

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
    public function load(int $userId, bool $withGroup, ConfiguratorInterface $configurator);

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
    public function loadByLogin(string $login, bool $withGroup, ConfiguratorInterface $configurator);

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
    public function loadByUuid(string $uuid, bool $withGroup, ConfiguratorInterface $configurator);

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
    public function loadByPhone(string $phone, bool $withGroup, ConfiguratorInterface $configurator);

    /**
     * Loads anonymous user
     *
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     *
     * @return \nPub\SPI\Persistence\User\User
     */
    public function loadAnonymous(ConfiguratorInterface $configurator);

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
    public function loadUserGroups(int $user_id);

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
    public function loadAdminGroups(int $user_id);

    /**
     * Check if the user login does not exist
     *
     * @param string $login
     * @return int
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasLogin(string $login);

    /**
     * Check if the user email does not exist
     *
     * @param string $email
     * @return int
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    //public function hasEmail($email);

    /**
     * Check if the user phone does not exist
     *
     * @param string $phone
     * @return int
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasPhone(string $phone);
}

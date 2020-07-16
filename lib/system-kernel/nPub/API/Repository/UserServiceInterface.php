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

use nPub\SPI\Persistence\User\User;
use nPub\SPI\Persistence\User\UserGroup;
use nPub\SPI\Persistence\Admin\AdminGroup;
use nPub\SPI\Persistence\User\UserCreateHelper;
use nPub\SPI\Persistence\User\UserUpdateHelper;

/**
 * @author wangbin
 */
interface UserServiceInterface
{
    /**
     * Instantiate a user create helper class, if given $userGroup, will create an user.
     *
     * @param \nPub\SPI\Persistence\User\UserGroup $userGroup  new user's User Group
     * @param nPub\SPI\Persistence\Admin\AdminGroup $adminGroup  new user's Admin Group
     *
     * @return \nPub\SPI\Persistence\User\UserCreateHelper
     */
    public function newUserCreateHelper(UserGroup $userGroup = null, AdminGroup $adminGroup = null);

    /**
     * Instantiate a user update helper class
     *
     * @param \nPub\SPI\Persistence\User\User $user  Need update user
     *
     * @return \nPub\SPI\Persistence\User\UserUpdateHelper
     */
    public function newUserUpdateHelper(User $user);

    /**
     * Create a new user.
     *
     * @param \nPub\SPI\Persistence\User\UserCreateHelper $userCreateHelper
     *
     * @return \nPub\SPI\Persistence\User\User|boolean  Return the create user if create success, or false when create failure
     *
     * @throws \nPub\Core\Base\Exceptions\UnverifiedException
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function createUser(UserCreateHelper $userCreateHelper);

    /**
     * Update the given user.
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
     *
     * @return boolean  Return true if update success, or false when update failure
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateUserPassword(User $user, $password);

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
     * Update user's admin group, can add some groups and delete some groups simultaneously
     *
     * @param \nPub\SPI\Persistence\User\UserUpdateHelper $userUpdateHelper
     *
     * @return boolean
     *
     * @throws \nPub\Core\Base\Exceptions\UnauthorizedException
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
     * @param int $userId  user id
     * @param bool $withGroup
     *
     * @return boolean|\nPub\SPI\Persistence\User\User
     *
     * @throws \nPub\Core\Base\Exceptions\InvalidArgumentValue
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadUser(int $userId, bool $withGroup = true);

    /**
     * Loads a admin user by the given user id.
     *
     * @param int $userId  user id
     * @param bool $withGroup
     *
     * @return boolean|\nPub\SPI\Persistence\User\User
     *
     * @throws \nPub\Core\Base\Exceptions\InvalidArgumentValue
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAdmin(int $userId, bool $withGroup = true);

    /**
     * Loads a user for the given login and password.
     *
     * @param string $login
     * @param string $password
     * @param bool $withGroup
     *
     * @return boolean|\nPub\SPI\Persistence\User\User
     *
     * @throws \nPub\Core\Base\Exceptions\InvalidArgumentValue
     * @throws \nPub\Core\Base\Exceptions\InvalidPassword
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadUserByCredentials(string $login, string $password, bool $withGroup = true);

    /**
     * Loads a user for the given login.
     *
     * @param string $login
     * @param bool $withGroup
     *
     * @return boolean|\nPub\SPI\Persistence\User\User
     *
     * @throws \nPub\Core\Base\Exceptions\InvalidArgumentValue
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadUserByLogin(string $login, bool $withGroup = true);

    /**
     * Loads users for the given telephone.
     *
     * @param string $phone
     * @param bool $withGroup
     *
     * @return boolean|\nPub\SPI\Persistence\User\User
     *
     * @throws \nPub\Core\Base\Exceptions\InvalidArgumentValue
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadUserByPhone(string $phone, bool $withGroup = true);

    /**
     * Loads a user for the given telephone.
     *
     * @param string $uuid
     * @param bool $withGroup
     *
     * @return boolean|\nPub\SPI\Persistence\User\User
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadUserByUuid(string $uuid, bool $withGroup = true);

    /**
     * Loads users for the given login token.
     *
     * @param string $token
     * @param string $salt
     * @param bool $withGroup
     *
     * @return boolean|\nPub\SPI\Persistence\User\User
     *
     * @throws \nPub\Core\Base\Exceptions\InvalidArgumentValue
     * @throws \nPub\Core\Base\Exceptions\InvalidPassword
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadUserByToken(string $token, string $salt = '', bool $withGroup = true);

    /**
     * Loads anonymous user
     *
     * @return \nPub\SPI\Persistence\User\User
     */
    public function loadAnonymousUser();

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
     * Loads current user
     *
     * @return \nPub\SPI\Persistence\User\User
     */
    public function loadCurrentUser();

    /**
     * Save the current user in cookie or session
     *
     * @param User $user
     * @param int $expire  expire time, Unit of time is second
     */
    public function localizeCurrentUser(User $user, int $expire = 0);

    /**
     * Generate a user token which is unique
     *
     * @param User $user
     * @param string $salt
     * @return string
     */
    public function generateToken(User $user, string $salt = '');

    /**
     * Decrypt token
     *
     * @param string $token
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @return null|array  return ['uid' => $uid, 'phone' => $phone, 'salt' => $salt, 'rand' => $rand];
     */
    public function decryptToken(string $token);

    /**
     * Destroy current user, it mean logout
     *
     * @return boolean
     */
    public function destroyCurrentUser();

    /**
     * Check if the user login does not exist
     *
     * @param string $login
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasLogin(string $login);

    /**
     * Check if the user phone does not exist
     *
     * @param string $phone
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasPhone(string $phone);

    /**
     * Get user storager.
     *
     * @return \Nopis\Lib\Security\User\UserStoragerInterface
     */
    public function getUserStorager();

    /**
     * Get user Encryptor.
     *
     * @return \Nopis\Lib\Security\User\UserEncryptorInterface
     */
    public function getUserEncryptor();
}

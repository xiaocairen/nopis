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

use nPub\API\Repository\UserServiceInterface;
use nPub\API\Repository\RepositoryInterface;
use nPub\SPI\Persistence\User\UserHandlerInterface;
use nPub\SPI\Persistence\User\UserCreateHelper;
use nPub\SPI\Persistence\User\UserUpdateHelper;
use nPub\SPI\Persistence\User\User;
use nPub\SPI\Persistence\User\UserGroup;
use nPub\SPI\Persistence\Admin\AdminGroup;
use nPub\Core\Base\Exceptions\InvalidArgumentValue;
use nPub\Core\Base\Exceptions\UnverifiedException;
use nPub\Core\Base\Exceptions\InvalidPassword;
use nPub\Core\Base\Exceptions\UnauthorizedException;

/**
 * Description of UserService
 *
 * @author wangbin
 */
class UserService implements UserServiceInterface
{
    /**
     * @var \nPub\API\Repository\RepositoryInterface
     */
    private $repository;

    /**
     *
     * @var \nPub\SPI\Persistence\User\UserHandlerInterface
     */
    private $userHanlder;

    /**
     * @var \Nopis\Lib\Security\User\UserStoragerInterface
     */
    private $userStorager;

    /**
     * @var \Nopis\Lib\Security\User\UserEncryptorInterface
     */
    private $userEncryptor;

    /**
     * @var \nPub\SPI\Persistence\User\User
     */
    private $currentUser;

    /**
     * Constructor.
     *
     * @param \nPub\API\Repository\RepositoryInterface $repository
     * @param \nPub\SPI\Persistence\User\UserHandlerInterface $userHandler
     */
    public function __construct(RepositoryInterface $repository, UserHandlerInterface $userHandler)
    {
        $this->repository = $repository;
        $this->userHanlder = $userHandler;
    }

    /**
     * Instantiate a user create helper class, if given $userGroup, will create an user.
     *
     * @param \nPub\SPI\Persistence\User\UserGroup $userGroup  new user's User Group
     * @param nPub\SPI\Persistence\Admin\AdminGroup $adminGroup  new user's Admin Group
     *
     * @return \nPub\SPI\Persistence\User\UserCreateHelper
     */
    public function newUserCreateHelper(UserGroup $userGroup = null, AdminGroup $adminGroup = null)
    {
        return $this->userHanlder->getUserCreateHelper($this->repository->getConfigurator(), $userGroup, $adminGroup);
    }

    /**
     * Instantiate a user update helper class
     *
     * @param \nPub\SPI\Persistence\User\User $user  Need update user
     *
     * @return \nPub\SPI\Persistence\User\UserUpdateHelper
     */
    public function newUserUpdateHelper(User $user)
    {
        return $this->userHanlder->getUserUpdateHelper($user);
    }

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
    public function createUser(UserCreateHelper $userCreateHelper)
    {
        $user = $userCreateHelper->getEntity();
        if (!$user->getUsername() || !$user->getPassword())
            throw new UnverifiedException('The username and password can\'t be empty');

        return $this->userHanlder->createUser($userCreateHelper, $this->getUserEncryptor());
    }

    /**
     * Update the given user.
     *
     * @param \nPub\SPI\Persistence\User\UserUpdateHelper $userUpdateHelper
     *
     * @return boolean  Return true if update success, or false when update failure
     *
     * @throws \nPub\Core\Base\Exceptions\UnauthorizedException
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateUser(UserUpdateHelper $userUpdateHelper)
    {
        return $this->userHanlder->updateUser($userUpdateHelper);
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
        return $this->userHanlder->updateUsername($user, $username);
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
        return $this->userHanlder->updateUserPhone($user, $phone);
    }

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
    public function updateUserPassword(User $user, $password)
    {
        return $this->userHanlder->updateUserPassword($user, $password, $this->getUserEncryptor());
    }

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
    public function updateUserGroups(UserUpdateHelper $userUpdateHelper)
    {
        return $this->userHanlder->updateUserGroups($userUpdateHelper);
    }

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
    public function updateAdminGroups(UserUpdateHelper $userUpdateHelper)
    {
        $user = $userUpdateHelper->getEntity();
        if (!$user->isAdmin()) {
            throw new UnauthorizedException('Not admin account');
        }

        return $this->userHanlder->updateAdminGroups($userUpdateHelper);
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
        return $this->userHanlder->updateUserLoginTime($user, $time);
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
        return $this->userHanlder->deleteUser($user);
    }

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
    public function loadUser(int $userId, bool $withGroup = true)
    {
        if ($userId <= 0)
            throw new InvalidArgumentValue('userId', $userId);

        return $this->userHanlder->load($userId, $withGroup, $this->repository->getConfigurator());
    }

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
    public function loadAdmin(int $userId, bool $withGroup = true)
    {
        if ($userId <= 0)
            throw new InvalidArgumentValue('userId', $userId);

        $user = $this->userHanlder->load($userId, $withGroup, $this->repository->getConfigurator());
        return !$user || !$user->isAdmin() ? false : $user;
    }

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
    public function loadUserByCredentials(string $login, string $password, bool $withGroup = true)
    {
        if (empty($login))
            throw new InvalidArgumentValue("login", $login);

        if (empty($password))
            throw new InvalidArgumentValue("password", $password);

        // Randomize login time to protect against timing attacks
        usleep(mt_rand(100, 300));

        $isMobile = function($mobile){
            if (!is_numeric($mobile)) {
                return false;
            }
            return preg_match('#^1[1|2|3|4|5|6|7|8|9]{1}\d{9}$#', $mobile) ? true : false;
        };

        // support phone email
        if ($isMobile($login)) {
            $user = $this->userHanlder->loadByPhone($login, $withGroup, $this->repository->getConfigurator());
        } else {
            $user = $this->userHanlder->loadByLogin($login, $withGroup, $this->repository->getConfigurator());
        }

        if (!$user) {
            return false;
        }

        if (!$this->getUserEncryptor()->verifyPassword($user, $password)) {
            throw new InvalidPassword;
        }

        return $user;
    }

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
    public function loadUserByLogin(string $login, bool $withGroup = true)
    {
        if ( empty($login) )
            throw new InvalidArgumentValue('login', $login);

        return $this->userHanlder->loadByLogin($login, $withGroup, $this->repository->getConfigurator());
    }

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
    public function loadUserByPhone(string $phone, bool $withGroup = true)
    {
        if (empty($phone))
            throw new InvalidArgumentValue('phone', $phone);

        return $this->userHanlder->loadByPhone($phone, $withGroup, $this->repository->getConfigurator());
    }

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
    public function loadUserByUuid(string $uuid, bool $withGroup = true)
    {
        return $this->userHanlder->loadByUuid($uuid, $withGroup, $this->repository->getConfigurator());
    }

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
    public function loadUserByToken(string $token, string $salt = '', bool $withGroup = true)
    {
        $dec = $this->decryptToken($token);
        if (!$dec)
            throw new InvalidArgumentValue('token', $token);

        $user = $this->userHanlder->load($dec['uid'], $withGroup, $this->repository->getConfigurator());
        if (!$user || $user->phone != $dec['phone']) {
            return false;
        }
        if ($salt) {
            $salt = strtr($salt, ':', '-');
            if ($dec['salt'] != $salt) {
                return false;
            }
        }

        return $user;
    }

    /**
     * Loads anonymous user
     *
     * @return \nPub\SPI\Persistence\User\User
     */
    public function loadAnonymousUser()
    {
        return $this->userHanlder->loadAnonymous($this->repository->getConfigurator());
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
        return $this->userHanlder->loadUserGroups($user_id);
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
        return $this->userHanlder->loadAdminGroups($user_id);
    }

    /**
     * Loads current user
     *
     * @return \nPub\SPI\Persistence\User\User
     */
    public function loadCurrentUser()
    {
        if (null === $this->currentUser) {
            $storagedUserCredentials = $this->getStoragedUserCredentials();
            if (!$storagedUserCredentials instanceof \stdClass) {
                $this->currentUser = $this->loadAnonymousUser();
            } else {
                try {
                    if (false === ($user = $this->loadUserByLogin($storagedUserCredentials->login))) {
                        throw new \Exception;
                    }
                    $this->currentUser = $user->getPassword() === $storagedUserCredentials->credentials ? $user : $this->loadAnonymousUser();
                } catch (\Exception $e) {
                    $this->currentUser = $this->loadAnonymousUser();
                }
            }
        }

        return $this->currentUser;
    }

    /**
     * Save the current user in cookie or session
     *
     * @param User $user
     * @param int $expire  expire time, Unit of time is second
     */
    public function localizeCurrentUser(User $user, int $expire = 0)
    {
        if ($user->isMember() || $user->isAdmin()) {
            $this->saveCurrentUserCredentials($user->getUsername(), $user->getPassword(), $expire);
        }
    }

    /**
     * Generate a user token which is unique
     *
     * @param User $user
     * @param string $salt
     * @return string
     */
    public function generateToken(User $user, string $salt = '')
    {
        if ($user->isAnonymous())
            return '';

        $rand = mt_rand(10, 99);
        $salt = $salt ? strtr($salt, ':', '-') : substr($user->last_login_time, -3);
        $token = $rand . ':' . $salt . ':' . $user->getUserId() . ':' . $user->getPhone();
        return $this->getUserEncryptor()->generateToken($token, $this->repository->getConfigurator());
    }

    /**
     * Decrypt token
     *
     * @param string $token
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @return null|array return ['uid' => $uid, 'phone' => $phone, 'salt' => $salt, 'rand' => $rand];
     */
    public function decryptToken(string $token)
    {
        if (!$token)
            return null;

        $dec = $this->getUserEncryptor()->decryptToken($token, $this->repository->getConfigurator());
        if (!$dec) {
            return null;
        }
        list($rand, $salt, $uid, $phone) = explode(':', $dec);
        if (!is_numeric($uid) || $uid <= 0 || !$phone) {
            return null;
        }

        return ['uid' => $uid, 'phone' => $phone, 'salt' => $salt, 'rand' => $rand];
    }

    /**
     * Destroy current user, it mean logout
     *
     * @return boolean
     */
    public function destroyCurrentUser()
    {
        return $this->getUserStorager()->destroyCurrentUserCredentials();
    }

    /**
     * Check if the user login does not exist
     *
     * @param string $login
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasLogin(string $login)
    {
        if (!$login)
            return false;

        return (boolean)$this->userHanlder->hasLogin($login);
    }

    /**
     * Check if the user phone does not exist
     *
     * @param string $phone
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasPhone(string $phone)
    {
        if (!$phone)
            return false;

        return (boolean)$this->userHanlder->hasPhone($phone);
    }

    /**
     * Get user storager.
     *
     * @return \Nopis\Lib\Security\User\UserStoragerInterface
     */
    public function getUserStorager()
    {
        if (null == $this->userStorager) {
            $this->userStorager = $this->repository->getContainer()->get('nPub.user.storager');
        }
        return $this->userStorager;
    }

    /**
     * Get user Encryptor.
     *
     * @return \Nopis\Lib\Security\User\UserEncryptorInterface
     */
    public function getUserEncryptor()
    {
        if (null == $this->userEncryptor) {
            $this->userEncryptor = $this->repository->getContainer()->get('nPub.user.encryptor');
        }
        return $this->userEncryptor;
    }

    /**
     * @param string $login
     * @param string $credentials
     * @param int $expire
     * @return boolean
     */
    protected function saveCurrentUserCredentials($login, $credentials, $expire)
    {
        $login = $login . '$' . time();
        return $this->getUserStorager()->saveCurrentUserCredentials($login, $credentials, $expire);
    }

    /**
     * @return boolean|\stdClass
     */
    protected function getStoragedUserCredentials()
    {
        $storagedUserCredentials = $this->getUserStorager()->getStoragedUserCredentials();
        if (!$storagedUserCredentials instanceof \stdClass) {
            return false;
        }

        $storagedUserCredentials->login = substr($storagedUserCredentials->login, 0, strrpos($storagedUserCredentials->login, '$'));

        return $storagedUserCredentials;
    }
}

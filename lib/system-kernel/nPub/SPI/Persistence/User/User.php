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


use nPub\SPI\Persistence\Entity\User\User as SPIUser;
use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\Database\TableInterface;
use Nopis\Lib\Security\User\UserInterface;
use Nopis\Lib\Security\User\Role\RoleInterface;
use Nopis\Lib\Security\User\Role\Admin;
use Nopis\Lib\Security\User\Role\Member;
use Nopis\Lib\Security\User\Role\Anonymous;
use Nopis\Lib\Security\User\Acl\Policy;

/**
 * Description of User
 *
 * @author wangbin
 */
class User extends SPIUser implements TableInterface, UserInterface
{
    const ROLER_MEMBER = 0;
    const ROLER_ADMINISTRATOR = 9;

    /**
     * @var \Nopis\Lib\Security\User\Role\RoleInterface
     */
    private $__role;

    /**
     * @var \nPub\SPI\Persistence\User\UserGroup[]
     */
    public $userGroups = [];

    /**
     * @var \nPub\SPI\Persistence\Admin\AdminGroup[]
     */
    public $adminGroups = [];

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @param array $properties
     */
    public function __construct(ConfiguratorInterface $configurator, array $properties = array())
    {
        $this->userGroups = isset($properties['userGroups']) ? $properties['userGroups'] : [];
        $this->adminGroups = isset($properties['adminGroups']) ? $properties['adminGroups'] : [];
        unset($properties['__role'], $properties['userGroups'], $properties['adminGroups']);

        parent::__construct($properties);
        if (!$this->__role instanceof RoleInterface) {
            $policy = new Policy($configurator);
            $this->__role = $this->roler == self::ROLER_ADMINISTRATOR ? new Admin($policy) : ($this->user_id  ? new Member($policy) : new Anonymous($policy));
        }
    }

    /**
     * 用户角色
     *
     * @return \Nopis\Lib\Security\User\Role\RoleInterface
     */
    public function role()
    {
        return $this->__role;
    }

    /**
     * 返回用户所属的所有用户组
     *
     * @return \nPub\SPI\Persistence\User\UserGroup[]
     */
    public function getUserGroups()
    {
        return $this->userGroups ?: [];
    }

    /**
     * 返回用户所属的所有管理员组
     *
     * @return \nPub\SPI\Persistence\Admin\AdminGroup[]
     */
    public function getAdminGroups()
    {
        return $this->adminGroups ?: [];
    }

    /**
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->__role instanceof Admin;
    }

    /**
     * @return boolean
     */
    public function isMember()
    {
        return $this->__role instanceof Member;
    }

    /**
     * @return boolean
     */
    public function isAnonymous()
    {
        return $this->__role instanceof Anonymous;
    }

    /**
     * @return boolean
     */
    public function isSuper()
    {
        if ($this->user_id != 1)
            return false;

        return $this->inSuperGroup();
    }

    /**
     * @return boolean
     */
    public function inSuperGroup()
    {
        if (null !== $this->adminGroups) {
            foreach ($this->adminGroups as $group) {
                if ($group->isSuperGroup()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 是否禁用
     *
     * @return boolean
     */
    public function isForbid()
    {
        return (boolean) $this->is_forbid;
    }

    /**
     * @return boolean
     */
    public function isDel()
    {
        return (boolean) $this->is_del;
    }

    /**
     * 用户ID
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * 用户名
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * 移动电话
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * 密码
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * 真实姓名
     *
     * @return string
     */
    public function getRealName()
    {
        return $this->realname;
    }

    /**
     * 性别
     *
     * @return enum('man','woman','secret')
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * 用户身份
     *
     * @return int
     */
    public function getRoler()
    {
        return $this->roler;
    }

    /**
     * 注册时间
     *
     * @return int
     */
    public function getRegTime()
    {
        return $this->reg_time;
    }

    /**
     * 最后登陆时间
     *
     * @return int
     */
    public function getLastLoginTime()
    {
        return $this->last_login_time;
    }
}

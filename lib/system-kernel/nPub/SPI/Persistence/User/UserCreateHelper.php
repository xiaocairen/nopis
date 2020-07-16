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

use nPub\SPI\Persistence\User\UserGroup;
use nPub\SPI\Persistence\Admin\AdminGroup;
use Nopis\Lib\Config\ConfiguratorInterface;
use nPub\SPI\Persistence\Entity\CreateHelper;

/**
 * Description of UserCreateHelper
 *
 * @author wangbin
 */
class UserCreateHelper extends CreateHelper
{

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    private $configurator;

    /**
     * @var \nPub\SPI\Persistence\User\UserGroup|null
     */
    private $userGroup;

    /**
     * @var \nPub\SPI\Persistence\Admin\AdminGroup|null
     */
    private $adminGroup;

    /**
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @param \nPub\SPI\Persistence\User\UserGroup $userGroup
     */
    public function __construct(ConfiguratorInterface $configurator, UserGroup $userGroup = null, AdminGroup $adminGroup = null)
    {
        parent::__construct();
        $this->configurator = $configurator;
        $this->userGroup = $userGroup ?: null;
        $this->adminGroup = $adminGroup ?: null;
    }

    /**
     * Get user group
     *
     * @return \nPub\SPI\Persistence\User\UserGroup|null
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * Get admin group
     *
     * @return \nPub\SPI\Persistence\Admin\AdminGroup|null
     */
    public function getAdminGroup()
    {
        return $this->adminGroup;
    }

    /**
     * Get entity object
     *
     * @return \nPub\SPI\Persistence\User\User
     */
    public function getEntity()
    {
        return parent::getEntity();
    }

    /**
     * @return \Nopis\Lib\Config\ConfiguratorInterface
     */
    public function getConfigurator()
    {
        return $this->configurator;
    }

    /**
     * @param array $arguments
     */
    protected function setEntity(array $arguments)
    {
        unset($arguments['userGroups'], $arguments['adminGroups']);
        null === $this->userGroup || $arguments['userGroups'][] = $this->userGroup;
        null === $this->adminGroup || $arguments['adminGroups'][] = $this->adminGroup;

        $this->entity = new User($this->configurator, $arguments);
    }

}

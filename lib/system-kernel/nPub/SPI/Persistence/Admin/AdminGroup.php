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

use nPub\SPI\Persistence\Entity\Admin\Group as SPIGroup;
use Nopis\Lib\Database\TableInterface;

class AdminGroup extends SPIGroup implements TableInterface
{

    /**
     * @var \nPub\SPI\Persistence\Backend\BackendMap[]
     */
    private $backendMaps = [];

    /**
     * Constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        isset($properties['backendMaps']) && $this->backendMaps = $properties['backendMaps'];
        unset($properties['backendMaps']);

        parent::__construct($properties);
    }

    /**
     * @return \nPub\SPI\Persistence\Backend\BackendMap[]
     */
    public function getBackendMaps()
    {
        return $this->backendMaps;
    }

    /**
     * 用户组id
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * 组名称
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->group_name;
    }

    /**
     * 是否禁止
     *
     * @return boolean
     */
    public function isForbid()
    {
        return $this->is_forbid == 1;
    }

    /**
     * 是否为超级管理员组
     *
     * @return boolean
     */
    public function isSuperGroup()
    {
        return 1 == $this->group_id;
    }
}


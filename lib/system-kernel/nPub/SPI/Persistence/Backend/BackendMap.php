<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Backend;

use nPub\SPI\Persistence\Entity\Backend\BackendMap as SPIBackendMap;
use Nopis\Lib\Database\TableInterface;

/**
 * Description of BackendMap
 *
 * @author wb
 */
class BackendMap extends SPIBackendMap implements TableInterface
{
    /**
     * 菜单ID
     *
     * @return int
     */
    public function getMapId()
    {
        return $this->map_id;
    }

    /**
     * 父菜单id
     *
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * 菜单名称
     *
     * @return string
     */
    public function getMenuName()
    {
        return $this->menu_name;
    }

    /**
     * 菜单对应的action控制器
     *
     * @return string
     */
    public function getMenuAction()
    {
        return $this->menu_action;
    }

    /**
     * 菜单排序
     *
     * @return int
     */
    public function getMenuSort()
    {
        return $this->menu_sort;
    }

    /**
     * 是否显示到后台菜单，如果不显示到后台菜单，则仅作权限控制
     *
     * @return boolean
     */
    public function ifShow()
    {
        return $this->if_show;
    }

    /**
     * 菜单级别，只有1，2，3级
     *
     * @return int
     */
    public function getMenuLevel()
    {
        return $this->menu_level;
    }

    /**
     * 菜单根路径
     *
     * @return string
     */
    public function getRootPath()
    {
        return $this->root_path;
    }

    /**
     * @return boolean
     */
    public function isRoot()
    {
        return $this->pid == 0;
    }
}

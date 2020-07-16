<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Classify;

use nPub\SPI\Persistence\Entity\Classify\Classify as SPIClassify;
use Nopis\Lib\Database\TableInterface;

/**
 * Description of Classify
 *
 * @author wb
 */
class Classify extends SPIClassify implements TableInterface
{

    /**
     * 文件夹id
     *
     * @return int
     */
    public function getClassifyId()
    {
        return $this->classify_id;
    }

    /**
     * 父文件夹id
     *
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * 文件夹名
     *
     * @return string
     */
    public function getClassifyName()
    {
        return $this->classify_name;
    }

    /**
     * 文件夹类型标识
     *
     * @return string
     */
    public function getClassifyType()
    {
        return $this->classify_type;
    }

    /**
     * 描述
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * 文件夹根路径
     *
     * @return string
     */
    public function getRootPath()
    {
        return $this->root_path;
    }

    /**
     * 文件夹排序
     *
     * @return int
     */
    public function getSortIndex()
    {
        return $this->sort_index;
    }

    /**
     * 是否内置
     *
     * @return boolean
     */
    public function isBuiltin()
    {
        return (boolean) $this->is_builtin;
    }

    /**
     * 是否已删除
     *
     * @return boolean
     */
    public function isDeleted()
    {
        return (boolean) $this->is_deleted;
    }

    /**
     * 是否为根路径
     *
     * @return boolean
     */
    public function isRoot()
    {
        return $this->pid == 0;
    }
}

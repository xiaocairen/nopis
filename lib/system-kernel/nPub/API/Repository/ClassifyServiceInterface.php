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

use nPub\SPI\Persistence\Classify\Classify;
use nPub\SPI\Persistence\Classify\ClassifyCreateHelper;
use nPub\SPI\Persistence\Classify\ClassifyUpdateHelper;

/**
 * @author wangbin
 */
interface ClassifyServiceInterface
{
    /**
     * Instantiate a Classify create helper class
     *
     * @return \nPub\SPI\Persistence\Classify\ClassifyCreateHelper
     */
    public function newClassifyCreateHelper();

    /**
     * Instantiate a Classify update helper class
     *
     * @param \nPub\SPI\Persistence\Classify\Classify $classify  Need update Classify
     * @return \nPub\SPI\Persistence\Classify\ClassifyUpdateHelper
     */
    public function newClassifyUpdateHelper(Classify $classify);

    /**
     * Create a new Classify.
     *
     * @param \nPub\SPI\Persistence\Classify\ClassifyCreateHelper $classifyCreateHelper
     *
     * @return int|boolean  return the new Classify map_id if create success
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \nPub\Core\Base\Exceptions\NotFoundException
     */
    public function createClassify(ClassifyCreateHelper $classifyCreateHelper);

    /**
     * Update the given Classify.
     *
     * @param \nPub\SPI\Persistence\Classify\ClassifyCreateHelper $classifyUpdateHelper
     * @param \nPub\SPI\Persistence\Classify\Classify $newParent
     *
     * @return boolean Return true if update Classify success, or false when update failure
     *
     * @throws \nPub\Core\Base\Exceptions\UnsupportOperationException
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateClassify(ClassifyUpdateHelper $classifyUpdateHelper, Classify $newParent = null);

    /**
     * Delete a Classify.
     *
     * @param \nPub\SPI\Persistence\Classify\Classify $classify
     *
     * @return boolean  Return true if delete success, or false when delete failure
     *
     * @throws \nPub\Core\Base\Exceptions\UnsupportOperationException
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function deleteClassify(Classify $classify);

    /**
     * Loads a Classify by the given classify id.
     *
     * @param int $classifyId
     *
     * @return boolean|\nPub\SPI\Persistence\Classify\Classify
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function load(int $classifyId);

    /**
     * Load all Classify.
     *
     * @return array
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAll();

    /**
     * Loads a tree of all Classify.
     *
     * @return array
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \Exception
     */
    public function loadTree();

    /**
     * Load all classifys by classify type.
     *
     * @return \nPub\SPI\Persistence\Classify\Classify[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadClassifysByType(string $classifyType);

    /**
     * Change the parent node of Classify.
     *
     * @param \nPub\SPI\Persistence\Classify\Classify $classify
     * @param \nPub\SPI\Persistence\Classify\Classify $newParent
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \nPub\Core\Base\Exceptions\UnsupportOperationException
     */
    public function changeParentNode(Classify $classify, Classify $newParent);

    /**
     * Return the number of child classify.
     *
     * @param int $classifyId
     * @return int
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasSubNodes(int $classifyId);

    /**
     * Return a list child classify.
     *
     * @param int|array $classifyId
     * @return \nPub\SPI\Persistence\Classify\Classify[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadSubNodes($classifyId = null);

    /**
     * Return all sub classify.
     *
     * @param int $classifyId
     * @return \nPub\SPI\Persistence\Classify\Classify[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAllSubNodes(int $classifyId);

    /**
     * Return all parent classifys.
     *
     * @param \nPub\SPI\Persistence\Classify\Classify $classify
     * @return \nPub\SPI\Persistence\Classify\Classify[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAllParents(Classify $classify);

    /**
     * Build the options of select
     *
     * @param array $classifyTree
     * @param int $selected
     * @param int $level
     * @return string
     */
    public function buildOptions(array $classifyTree, $selected = null, $level = 0);

    /**
     * set database table
     *
     * @param string $table
     */
    public function setTable(string $table);
}

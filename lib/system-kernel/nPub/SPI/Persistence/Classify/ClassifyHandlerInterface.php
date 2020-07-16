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

/**
 * @author wangbin
 */
interface ClassifyHandlerInterface
{
    /**
     * Instantiate a Classify create helper class
     *
     * @return \nPub\SPI\Persistence\Classify\ClassifyCreateHelper
     */
    public function getClassifyCreateHelper();

    /**
     * Instantiate a Classify update helper class
     *
     * @param \nPub\SPI\Persistence\Classify\Classify $classify  Need update Classify
     * @return \nPub\SPI\Persistence\Classify\ClassifyUpdateHelper
     */
    public function getClassifyUpdateHelper(Classify $classify);

    /**
     * Create a new Classify.
     *
     * @param \nPub\SPI\Persistence\Classify\ClassifyCreateHelper $classifyCreateHelper
     * @param \nPub\SPI\Persistence\Classify\Classify $parent
     *
     * @return int|boolean  return the new Classify map_id if create success
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function createClassify(ClassifyCreateHelper $classifyCreateHelper, Classify $parent = null);

    /**
     * Update the given Classify.
     *
     * @param \nPub\SPI\Persistence\Classify\ClassifyCreateHelper $classifyUpdateHelper
     * @param \nPub\SPI\Persistence\Classify\Classify $newParent
     *
     * @return boolean   Return true if update Classify success, or false when update failure
     *
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
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function deleteClassify(Classify $classify);

    /**
     * Clear contents in classify, if $thorough is true, delete thorough.
     *
     * @param \nPub\SPI\Persistence\Classify\Classify $classify
     * @param boolean $thorough
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    //public function clearClassifyContents(Classify $classify, $thorough = false);

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
     *
     * Load all classifys by classify type.
     *
     * @param string $classifyType
     *
     * @return \nPub\SPI\Persistence\Classify\Classify[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadClassifysByType(string $classifyType);

    /**
     * Load contents list.
     *
     * @param int $classifyId
     * @param int $curPage
     * @param int $pageSize
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     *
     * @return \Nopis\Lib\Pagination\Paginator
     */
    //public function loadContents($classifyId, $curPage = 1, $pageSize = 20);

    /**
     * Load contents paginator.
     *
     * @param \Nopis\Lib\Pagination\Query\QueryAdapter $queryAdapter
     * @param int $curPage
     * @param int $pageSize
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     *
     * @return \Nopis\Lib\Pagination\Paginator
     */
    //public function loadContentsPaginator(QueryAdapter $queryAdapter, $curPage = 1, $pageSize = 20);

    /**
     * Change the parent node of Classify.
     *
     * @param \nPub\SPI\Persistence\Classify\Classify $classify
     * @param \nPub\SPI\Persistence\Classify\Classify $newParent
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function changeParentNode(Classify $classify, Classify $newParent);

    /**
     * Return the number of contents in classify by given.
     *
     * @param int $classifyId
     * @return int
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    //public function hasContents($classifyId);

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
     * @param int $select
     * @param int $level
     * @return string
     */
    public function buildOptions(array $classifyTree, $select = null, $level = 0);

    /**
     * set database table
     *
     * @param string $table
     */
    public function setTable(string $table);

    /**
     * get database table
     *
     * @return string
     */
    public function getTable();
}

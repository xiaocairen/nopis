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

use nPub\SPI\Persistence\Backend\BackendMap;
use nPub\SPI\Persistence\Backend\BackendMapCreateHelper;
use nPub\SPI\Persistence\Backend\BackendMapUpdateHelper;

/**
 *
 * @author wb
 */
interface BackendMapServiceInterface
{
    /**
     * Instantiate a BackendMap create helper class
     *
     * @return \nPub\SPI\Persistence\Backend\BackendMapCreateHelper
     */
    public function newBackendMapCreateHelper();

    /**
     * Instantiate a BackendMap update helper class
     *
     * @param \nPub\SPI\Persistence\Backend\BackendMap $backendMap
     *
     * @return \nPub\SPI\Persistence\Backend\BackendMapUpdateHelper
     */
    public function newBackendMapUpdateHelper(BackendMap $backendMap);

    /**
     * Create a new BackendMap.
     *
     * @param \nPub\SPI\Persistence\Backend\BackendMapCreateHelper $backendMapCreateHelper
     *
     * @return int|boolean  return the new map id if create success
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \nPub\Core\Base\Exceptions\NotFoundException
     * @throws \nPub\Core\Base\Exceptions\UnsupportOperationException
     */
    public function createBackendMap(BackendMapCreateHelper $backendMapCreateHelper);

    /**
     * Update the given BackendMap.
     *
     * @param \nPub\SPI\Persistence\Backend\BackendMapUpdateHelper $backendMapUpdateHelper
     * @param \nPub\SPI\Persistence\Backend\BackendMap $newParent
     *
     * @return boolean  Return true if update success, or false when update failure
     *
     * @throws \nPub\Core\Base\Exceptions\UnsupportOperationException
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateBackendMap(BackendMapUpdateHelper $backendMapUpdateHelper, BackendMap $newParent = null);

    /**
     * This method deletes a BackendMap.
     *
     * @param \nPub\SPI\Persistence\Backend\BackendMap $backendMap
     *
     * @return boolean  Return true if delete success, or false when delete failure
     *
     * @throws \nPub\Core\Base\Exceptions\UnsupportOperationException
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function deleteBackendMap(BackendMap $backendMap);

    /**
     * Loads a BackendMap by the given map id.
     *
     * @param int $mapId
     *
     * @return boolean|\nPub\SPI\Persistence\Backend\BackendMap
     *
     * @throws \nPub\Core\Base\Exceptions\InvalidArgumentValue
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function load(int $mapId);

    /**
     * Load all BackendMap.
     *
     * @return array
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAll();

    /**
     * Loads a tree of all BackendMap.
     *
     * @return array
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \Exception
     */
    public function loadTree();

    /**
     * Build all BackendMap to tree.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function buildTree(array $backendMaps);

    /**
     * Change the parent node of BackendMap.
     *
     * @param \nPub\SPI\Persistence\Backend\BackendMap $backendMap
     * @param \nPub\SPI\Persistence\Backend\BackendMap $newParent
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \nPub\Core\Base\Exceptions\UnsupportOperationException
     */
    public function changeParentNode(BackendMap $backendMap, BackendMap $newParent);

    /**
     * Return the number of child backend maps.
     *
     * @return int
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasChildNode(BackendMap $backendMap);

    /**
     * Return a list child backend map.
     *
     * @return \nPub\SPI\Persistence\Backend\BackendMap[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function getChildNode(BackendMap $backendMap);
}

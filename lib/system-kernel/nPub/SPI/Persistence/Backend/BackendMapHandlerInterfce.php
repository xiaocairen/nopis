<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace nPub\SPI\Persistence\Backend;

/**
 * @author wb
 */
interface BackendMapHandlerInterfce
{
    /**
     * Instantiate a BackendMap create helper class
     *
     * @return \nPub\SPI\Persistence\Backend\BackendMapCreateHelper
     */
    public function getBackendMapCreateHelper();

    /**
     * Instantiate a BackendMap update helper class
     *
     * @param \nPub\SPI\Persistence\Backend\BackendMap $backendMap  Need update BackendMap
     * @return \nPub\SPI\Persistence\Backend\BackendMapUpdateHelper
     */
    public function getBackendMapUpdateHelper(BackendMap $backendMap);

    /**
     * Create a new BackendMap.
     *
     * @param \nPub\SPI\Persistence\Backend\BackendMapCreateHelper $backendMapCreateHelper
     * @param \nPub\SPI\Persistence\Backend\BackendMap $parent
     *
     * @return int|boolean  return the new BackendMap map_id if create success
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function createBackendMap(BackendMapCreateHelper $backendMapCreateHelper, BackendMap $parent = null);

    /**
     * Update the given BackendMap.
     *
     * @param \nPub\SPI\Persistence\Backend\BackendMapCreateHelper $backendMapUpdateHelper
     * @param \nPub\SPI\Persistence\Backend\BackendMap $newParent
     *
     * @return boolean  Return true if update success, or false when update failure
     *
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
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function deleteBackendMap(BackendMap $backendMap);

    /**
     * Loads a BackendMap by the given backend map id.
     *
     * @param int $mapId
     *
     * @return boolean|\nPub\SPI\Persistence\Backend\BackendMap
     *
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

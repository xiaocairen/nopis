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

use nPub\API\Repository\BackendMapServiceInterface;
use nPub\API\Repository\RepositoryInterface;
use nPub\SPI\Persistence\Backend\BackendMapHandlerInterfce;
use nPub\SPI\Persistence\Backend\BackendMap;
use nPub\SPI\Persistence\Backend\BackendMapCreateHelper;
use nPub\SPI\Persistence\Backend\BackendMapUpdateHelper;
use nPub\Core\Base\Exceptions\UnsupportOperationException;
use nPub\Core\Base\Exceptions\NotFoundException;

/**
 * Description of BackendMapService
 *
 * @author wb
 */
class BackendMapService implements BackendMapServiceInterface
{
    /**
     * @var \nPub\API\Repository\RepositoryInterface
     */
    private $repository;

    /**
     *
     * @var \nPub\SPI\Persistence\Backend\BackendMapHandlerInterfce
     */
    private $backendMapHanlder;

    /**
     * Constructor.
     *
     * @param \nPub\API\Repository\RepositoryInterface $repository
     * @param \nPub\SPI\Persistence\Backend\BackendMapHandlerInterfce $backendMapHanlder
     */
    public function __construct(RepositoryInterface $repository, BackendMapHandlerInterfce $backendMapHanlder)
    {
        $this->repository = $repository;
        $this->backendMapHanlder = $backendMapHanlder;
    }

    /**
     * Instantiate a BackendMap create helper class
     *
     * @return \nPub\SPI\Persistence\Backend\BackendMapCreateHelper
     */
    public function newBackendMapCreateHelper()
    {
        return $this->backendMapHanlder->getBackendMapCreateHelper();
    }

    /**
     * Instantiate a BackendMap update helper class
     *
     * @param \nPub\SPI\Persistence\Backend\BackendMap $backendMap
     *
     * @return \nPub\SPI\Persistence\Backend\BackendMapUpdateHelper
     */
    public function newBackendMapUpdateHelper(BackendMap $backendMap)
    {
        return $this->backendMapHanlder->getBackendMapUpdateHelper($backendMap);
    }

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
    public function createBackendMap(BackendMapCreateHelper $backendMapCreateHelper)
    {
        $backendMap = $backendMapCreateHelper->getEntity();
        $parent     = null;
        if (!$backendMap->isRoot()) {
            $parent = $this->backendMapHanlder->load($backendMap->getPid());
            if (!$parent) {
                throw new NotFoundException('parent backendMap');
            }
            if ($parent->getMenuLevel() > 2) {
                throw new UnsupportOperationException('Backend map support max level is 3');
            }
        }

        return $this->backendMapHanlder->createBackendMap($backendMapCreateHelper, $parent);
    }

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
    public function updateBackendMap(BackendMapUpdateHelper $backendMapUpdateHelper, BackendMap $newParent = null)
    {
        if ($newParent) {
            $backendMap = $backendMapUpdateHelper->getEntity();
            if ($backendMap->getPid() == $newParent->getMapId()) {
                throw new UnsupportOperationException('Parent node not change');
            }
            $plevel = $newParent->getMenuLevel();
            if ($plevel > 2) {
                throw new UnsupportOperationException('New parent Backend map level is ' . $plevel);
            }
            if ($this->backendMapHanlder->hasChildNode($backendMap) && $plevel > 1) {
                throw new UnsupportOperationException('Backend map support max level is 3');
            }
        }

        return $this->backendMapHanlder->updateBackendMap($backendMapUpdateHelper, $newParent);
    }

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
    public function deleteBackendMap(BackendMap $backendMap)
    {
        if ($this->backendMapHanlder->hasChildNode($backendMap)) {
            throw new UnsupportOperationException('Unable to delete a Backend map which has sub node');
        }

        return $this->backendMapHanlder->deleteBackendMap($backendMap);
    }

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
    public function load(int $mapId)
    {
        if ($mapId <= 0)
            false;

        return $this->backendMapHanlder->load($mapId);
    }

    /**
     * Load all BackendMap.
     *
     * @return array
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAll()
    {
        return $this->backendMapHanlder->loadAll();
    }

    /**
     * Loads a tree of all BackendMap.
     *
     * @return array
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \Exception
     */
    public function loadTree()
    {
        return $this->backendMapHanlder->loadTree();
    }

    /**
     * Build all BackendMap to tree.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function buildTree(array $backendMaps)
    {
        return $this->backendMapHanlder->buildTree($backendMaps);
    }

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
    public function changeParentNode(BackendMap $backendMap, BackendMap $newParent)
    {
        if ($backendMap->getPid() == $newParent->getMapId()) {
            throw new UnsupportOperationException('Parent node not change');
        }
        $plevel = $newParent->getMenuLevel();
        if ($plevel > 2) {
            throw new UnsupportOperationException('New parent Backend map level is ' . $plevel);
        }
        if ($this->backendMapHanlder->hasChildNode($backendMap) && $plevel > 1) {
            throw new UnsupportOperationException('Backend map support max level is 3');
        }

        return $this->backendMapHanlder->changeParentNode($backendMap, $newParent);
    }

    /**
     * Return the number of child backend maps.
     *
     * @return int
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasChildNode(BackendMap $backendMap)
    {
        return $this->backendMapHanlder->hasChildNode($backendMap);
    }

    /**
     * Return a list child backend map.
     *
     * @return \nPub\SPI\Persistence\Backend\BackendMap[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function getChildNode(BackendMap $backendMap)
    {
        return $this->backendMapHanlder->getChildNode($backendMap);
    }
}

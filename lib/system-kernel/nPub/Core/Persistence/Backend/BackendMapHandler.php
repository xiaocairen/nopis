<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\Persistence\Backend;

use Nopis\Lib\Database\DBInterface;
use nPub\SPI\Persistence\Backend\BackendMap;
use nPub\SPI\Persistence\Backend\BackendMapHandlerInterfce;
use nPub\SPI\Persistence\Backend\BackendMapCreateHelper;
use nPub\SPI\Persistence\Backend\BackendMapUpdateHelper;

/**
 * Description of BackendMapHandler
 *
 * @author wb
 */
class BackendMapHandler implements BackendMapHandlerInterfce
{
    /**
     * @var \Nopis\Lib\Database\DBInterface
     */
    private $pdo;

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\Database\DBInterface $pdo
     */
    public function __construct(DBInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Instantiate a BackendMap create helper class
     *
     * @return \nPub\SPI\Persistence\Backend\BackendMapCreateHelper
     */
    public function getBackendMapCreateHelper()
    {
        return new BackendMapCreateHelper();
    }

    /**
     * Instantiate a BackendMap update helper class
     *
     * @param \nPub\SPI\Persistence\Backend\BackendMap $backendMap  Need update BackendMap
     * @return \nPub\SPI\Persistence\Backend\BackendMapUpdateHelper
     */
    public function getBackendMapUpdateHelper(BackendMap $backendMap)
    {
        return new BackendMapUpdateHelper($backendMap);
    }

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
    public function createBackendMap(BackendMapCreateHelper $backendMapCreateHelper, BackendMap $parent = null)
    {
        $backendMap = $backendMapCreateHelper->getEntity();
        $fields = $backendMapCreateHelper->getCreationFieldsValues();
        if (null !== $parent && !$backendMap->isRoot() && $backendMap->getPid() == $parent->getMapId()) {
            $fields['root_path']  = rtrim($parent->getRootPath(), '/') . '/' . $parent->getMapId();
            $fields['menu_level'] = $parent->getMenuLevel() + 1;
        }

        if (!$this->pdo->insert(BackendMap::tableName())->values($fields)->exec()) {
            return false;
        }

        return $this->pdo->lastInsertId();
    }

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
    public function updateBackendMap(BackendMapUpdateHelper $backendMapUpdateHelper, BackendMap $newParent = null)
    {
        $backendMap = $backendMapUpdateHelper->getEntity();
        $fields = $backendMapUpdateHelper->getUpdationFieldsValues();

        $in = $this->pdo->inTransaction();
        $in || $this->pdo->beginTransaction();

        $where = ['map_id', '=', $backendMap->getMapId()];
        if (false === $this->pdo->update(BackendMap::tableName())->set($fields)->where($where)->exec()) {
            $this->pdo->rollBack();
            return false;
        }
        if ($newParent && !$this->changeParentNode($backendMap, $newParent)) {
            $this->pdo->rollBack();
            return false;
        }
        $in || $this->pdo->commit();

        return true;
    }

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
    public function deleteBackendMap(BackendMap $backendMap)
    {
        return (boolean) $this->pdo->delete()->from(BackendMap::tableName())
                ->where('map_id', '=', $backendMap->getMapId())
                ->exec();
    }

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
    public function load(int $mapId)
    {
        $query = $this->pdo->select()
                ->from(BackendMap::tableName())
                ->where('map_id', '=', $mapId)
                ->query();

        return $query->fetch('\nPub\SPI\Persistence\Backend\BackendMap');
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
        return $this->pdo->select()
                ->from(BackendMap::tableName())->orderBy([['menu_sort', 'ASC'], ['map_id', 'ASC']])
                ->query()->fetchAll();
    }

    /**
     * Load a tree of all BackendMap.
     *
     * @return array
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \Exception
     */
    public function loadTree()
    {
        $backendMaps = $this->pdo->select()
                ->from(BackendMap::tableName())->orderBy([['menu_sort', 'ASC'], ['map_id', 'ASC']])
                ->query()->fetchAll();

        return $this->buildTree($backendMaps);
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
        $tree = [];
        foreach ($backendMaps as $key => $map) {
            if ($map->menu_level == 1) {
                $tree[$map->map_id] = clone $map;
                unset($backendMaps[$key]);
            }
        }

        foreach ($backendMaps as $key => $map) {
            if ($map->menu_level == 2) {
                if (!isset($tree[$map->pid])) {
                    throw new \Exception('Backend maps internal error!');
                }
                $tree[$map->pid]->childs[$map->map_id] = clone $map;
                unset($backendMaps[$key]);
            }
        }

        foreach ($backendMaps as $map) {
            $rootPaths = explode('/', trim($map->root_path, '/'));
            if (!isset($tree[$rootPaths[0]]) || !isset($tree[$rootPaths[0]]->childs[$rootPaths[1]])) {
                throw new \Exception('Backend maps internal error!');
            }
            $tree[$rootPaths[0]]->childs[$rootPaths[1]]->childs[$map->map_id] = clone $map;
        }

        $bm_tree = [];
        foreach ($tree as $top_tree) {
            if (property_exists($top_tree, 'childs')) {
                foreach ($top_tree->childs as $key => $sub_tree) {
                    if (property_exists($sub_tree, 'childs')) {
                        $top_tree->childs[$key]->childs = array_values($sub_tree->childs);
                    }
                }
                $top_tree->childs = array_values($top_tree->childs);
            }
            $bm_tree[] = $top_tree;
        }

        return $bm_tree;
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
     */
    public function changeParentNode(BackendMap $backendMap, BackendMap $newParent)
    {
        $in = $this->pdo->inTransaction();
        $in || $this->pdo->beginTransaction();

        $fields = [
            'pid'        => $newParent->getMapId(),
            'root_path'  => rtrim($newParent->getRootPath(), '/') . '/' . $newParent->getMapId(),
            'menu_level' => $newParent->getMenuLevel() + 1,
        ];
        if (!$this->pdo->update(BackendMap::tableName())->set($fields)->where('map_id', '=', $backendMap->getMapId())->exec()) {
            $this->pdo->rollBack();
            return false;
        }

        $childNewRootPath = rtrim($fields['root_path'], '/') . '/' . $backendMap->getMapId();
        $childOldRootPath = rtrim($backendMap->getRootPath(), '/') . '/' . $backendMap->getMapId();
        if (false === $this->pdo->update(BackendMap::tableName())->set(['root_path' => $childNewRootPath])
                ->where('root_path', 'like', $childOldRootPath . '%')->exec()) {
            $this->pdo->rollBack();
            return false;
        }
        $in || $this->pdo->commit();

        return true;
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
        return $this->pdo->select()->from(BackendMap::tableName())
                ->where('pid', '=', $backendMap->getMapId())
                ->count();
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
        $query = $this->pdo->select()->from(BackendMap::tableName())
                    ->where('pid', '=', $backendMap->getMapId())
                    ->query();

        return $query->fetchAll('\nPub\SPI\Persistence\Backend\BackendMap');
    }
}

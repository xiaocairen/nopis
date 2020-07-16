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

use nPub\API\Repository\ClassifyServiceInterface;
use nPub\API\Repository\RepositoryInterface;
use nPub\SPI\Persistence\Classify\ClassifyHandlerInterface;
use nPub\SPI\Persistence\Classify\Classify;
use nPub\SPI\Persistence\Classify\ClassifyCreateHelper;
use nPub\SPI\Persistence\Classify\ClassifyUpdateHelper;
use nPub\Core\Base\Exceptions\NotFoundException;
use nPub\Core\Base\Exceptions\UnsupportOperationException;

/**
 * Description of ClassifyService
 *
 * @author wangbin
 */
class ClassifyService implements ClassifyServiceInterface
{

    /**
     * @var \nPub\API\Repository\RepositoryInterface
     */
    private $repository;

    /**
     *
     * @var \nPub\SPI\Persistence\Classify\ClassifyHandlerInterface
     */
    private $classifyHanlder;

    /**
     * Constructor.
     *
     * @param \nPub\API\Repository\RepositoryInterface $repository
     * @param \nPub\SPI\Persistence\Classify\ClassifyHandlerInterface $classifyHanlder
     */
    public function __construct(RepositoryInterface $repository, ClassifyHandlerInterface $classifyHanlder)
    {
        $this->repository = $repository;
        $this->classifyHanlder = $classifyHanlder;
    }

    /**
     * Instantiate a Classify create helper class
     *
     * @return \nPub\SPI\Persistence\Classify\ClassifyCreateHelper
     */
    public function newClassifyCreateHelper()
    {
        return $this->classifyHanlder->getClassifyCreateHelper();
    }

    /**
     * Instantiate a Classify update helper class
     *
     * @param \nPub\SPI\Persistence\Classify\Classify $classify  Need update Classify
     * @return \nPub\SPI\Persistence\Classify\ClassifyUpdateHelper
     */
    public function newClassifyUpdateHelper(Classify $classify)
    {
        return $this->classifyHanlder->getClassifyUpdateHelper($classify);
    }

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
    public function createClassify(ClassifyCreateHelper $classifyCreateHelper)
    {
        $classify = $classifyCreateHelper->getEntity();
        if ($classify->isBuiltin() && 0 != $classify->getPid()) {
            throw new UnsupportOperationException('Builtin classify must be top level classify');
        }
        $parent = null;
        if (!$classify->isRoot() && ($parent = $this->classifyHanlder->load($classify->getPid())) && !$parent) {
            throw new NotFoundException('parent classify');
        }

        return $this->classifyHanlder->createClassify($classifyCreateHelper, $parent);
    }

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
    public function updateClassify(ClassifyUpdateHelper $classifyUpdateHelper, Classify $newParent = null)
    {
        $classify = $classifyUpdateHelper->getEntity();
        if ($classify->isBuiltin()) {
            throw new UnsupportOperationException('Unable to update a classify which is builtin');
        }
        if ($newParent && $classify->getPid() == $newParent->getClassifyId()) {
            throw new UnsupportOperationException('Parent node not change');
        }

        return $this->classifyHanlder->updateClassify($classifyUpdateHelper, $newParent);
    }

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
    public function deleteClassify(Classify $classify)
    {
        if ($classify->isBuiltin()) {
            throw new UnsupportOperationException('Unable to delete a classify which is builtin');
        }
        if ($this->classifyHanlder->hasSubNodes($classify->getClassifyId())) {
            throw new UnsupportOperationException('Unable to delete a classify which has sub node');
        }

        return $this->classifyHanlder->deleteClassify($classify);
    }

    /**
     * Loads a Classify by the given classify id.
     *
     * @param int $classifyId
     *
     * @return boolean|\nPub\SPI\Persistence\Classify\Classify
     *
     * @throws \nPub\Core\Base\Exceptions\InvalidArgumentValue
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function load(int $classifyId)
    {
        return $this->classifyHanlder->load($classifyId);
    }

    /**
     * Load all Classify.
     *
     * @return array
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAll()
    {
        return $this->classifyHanlder->loadAll();
    }

    /**
     * Load a tree of all Classify.
     *
     * @return array
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \Exception
     */
    public function loadTree()
    {
        return $this->classifyHanlder->loadTree();
    }

    /**
     * Load all classifys by classify type.
     *
     * @return \nPub\SPI\Persistence\Classify\Classify[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadClassifysByType(string $classifyType)
    {
        return $this->classifyHanlder->loadClassifysByType($classifyType);
    }

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
    public function changeParentNode(Classify $classify, Classify $newParent)
    {
        if ($classify->getPid() == $newParent->getClassifyId()) {
            throw new UnsupportOperationException('Parent node not change');
        }
        return $this->classifyHanlder->changeParentNode($classify, $newParent);
    }

    /**
     * Return the number of child classify.
     *
     * @param int $classifyId
     * @return int
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function hasSubNodes(int $classifyId)
    {
        if ($classifyId <= 0) {
            return false;
        }

        return $this->classifyHanlder->hasSubNodes($classifyId);
    }

    /**
     * Return a list child classify.
     *
     * @param int|array $classifyId
     * @return \nPub\SPI\Persistence\Classify\Classify[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadSubNodes($classifyId = null)
    {
        if ($classifyId) {
            if (is_array($classifyId)) {
                array_walk($classifyId, function(&$v, $k) {
                    $v = (int) $v;
                });
            } else if (!is_numeric($classifyId) || $classifyId <= 0) {
                return [];
            }
        }

        return $this->classifyHanlder->loadSubNodes($classifyId);
    }

    /**
     * Return all sub classify.
     *
     * @param int $classifyId
     * @return \nPub\SPI\Persistence\Classify\Classify[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAllSubNodes(int $classifyId)
    {
        if ($classifyId <= 0) {
            return [];
        }

        return $this->classifyHanlder->loadAllSubNodes($classifyId);
    }

    /**
     * Return all parent classifys.
     *
     * @param \nPub\SPI\Persistence\Classify\Classify $classify
     * @return \nPub\SPI\Persistence\Classify\Classify[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAllParents(Classify $classify)
    {
        if (!$classify->getPid()) {
            return [];
        }

        return $this->classifyHanlder->loadAllParents($classify);
    }

    /**
     * Build the options of select
     *
     * @param array $classifyTree
     * @param int $selected
     * @param int $level
     * @return string
     */
    public function buildOptions(array $classifyTree, $selected = null, $level = 0)
    {
        return $this->classifyHanlder->buildOptions($classifyTree, $selected, $level);
    }

    /**
     * set database table
     *
     * @param string $table
     */
    public function setTable(string $table)
    {
        $table = trim($table);
        $table && $this->classifyHanlder->setTable($table);
    }
}

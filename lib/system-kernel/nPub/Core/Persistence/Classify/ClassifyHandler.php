<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\Persistence\Classify;

use nPub\SPI\Persistence\Classify\ClassifyHandlerInterface;
use Nopis\Lib\Database\DBInterface;
use nPub\SPI\Persistence\Classify\Classify;
use nPub\SPI\Persistence\Classify\ClassifyCreateHelper;
use nPub\SPI\Persistence\Classify\ClassifyUpdateHelper;

/**
 * Description of ClassifyHandler
 *
 * @author wangbin
 */
class ClassifyHandler implements ClassifyHandlerInterface
{
    /**
     * @var \Nopis\Lib\Database\DBInterface
     */
    private $pdo;

    /**
     * @var string
     */
    private $table;

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
     * Instantiate a Classify create helper class
     *
     * @return \nPub\SPI\Persistence\Classify\ClassifyCreateHelper
     */
    public function getClassifyCreateHelper()
    {
        return new ClassifyCreateHelper();
    }

    /**
     * Instantiate a Classify update helper class
     *
     * @param \nPub\SPI\Persistence\Classify\Classify $classify  Need update Classify
     * @return \nPub\SPI\Persistence\Classify\ClassifyUpdateHelper
     */
    public function getClassifyUpdateHelper(Classify $classify)
    {
        return new ClassifyUpdateHelper($classify);
    }

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
    public function createClassify(ClassifyCreateHelper $classifyCreateHelper, Classify $parent = null)
    {
        $classify = $classifyCreateHelper->getEntity();
        $fields = $classifyCreateHelper->getCreationFieldsValues();
        if ($parent != null && !$classify->isRoot() && $classify->getPid() == $parent->getClassifyId()) {
            $fields['root_path'] = rtrim($parent->getRootPath(), '/') . '/' . $parent->getClassifyId();
        }

        return $this->pdo->insert($this->getTable())
                ->values($fields)
                ->exec() ? $this->pdo->lastInsertId() : false;
    }

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
    public function updateClassify(ClassifyUpdateHelper $classifyUpdateHelper, Classify $newParent = null)
    {
        $classify = $classifyUpdateHelper->getEntity();
        $fields = $classifyUpdateHelper->getUpdationFieldsValues();

        $in = $this->pdo->inTransaction();
        $in || $this->pdo->beginTransaction();

        if (false === $this->pdo->update($this->getTable())->set($fields)->where('classify_id', '=', $classify->getClassifyId())->exec()) {
            $this->pdo->rollBack();
            return false;
        }

        if ($newParent && !$this->changeParentNode($classify, $newParent)) {
            $this->pdo->rollBack();
            return false;
        }
        $in || $this->pdo->commit();

        return true;
    }

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
    public function deleteClassify(Classify $classify)
    {
        if (!$classify->isDeleted()) {
            if (!$this->pdo->update($this->getTable())
                ->set(['is_deleted' => 1])
                ->where('classify_id', '=', $classify->getClassifyId())
                ->exec()) {
                return false;
            }
        } elseif (!$this->pdo->delete()->from($this->getTable())
                ->where('classify_id', '=', $classify->getClassifyId())
                ->exec()) {
            return false;
        }

        return true;
    }

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
    /*public function clearClassifyContents(Classify $classify, $thorough = false)
    {
        if ($thorough) {
            if (false === $this->pdo->delete()->from(ClassifyContent::tableName())
                    ->where(['classify_id', '=', $classify->getClassifyId()])
                    ->exec()) {
                return false;
            }
        } else {
            if (false === $this->pdo->update(ClassifyContent::tableName())->set(['in_trash' => 1])
                    ->where(['classify_id', '=', $classify->getClassifyId()])
                    ->exec()) {
                return false;
            }
        }

        return true;
    }*/

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
    public function load(int $classifyId)
    {
        $query = $this->pdo->select()->from($this->getTable())
                ->where('classify_id', '=', $classifyId)
                ->query();

        return $query->fetch('\nPub\SPI\Persistence\Classify\Classify');
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
        return $this->pdo->select()->from($this->getTable())
                ->orderBy([['sort_index', 'ASC'], ['classify_id', 'DESC']])
                ->query()->fetchAll();
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
        $classifys = $this->pdo->select()->from($this->getTable())->orderBy(['pid', 'ASC'])
                ->query()->fetchAll();

        $hasSubTree = function($classifys, $curPid){
            foreach ($classifys as $classify)
                if ($classify->pid == $curPid) return true;
            return false;
        };

        $buildTree = function ($classifys, $curPid = 0) use(& $buildTree, $hasSubTree){
            $tree = [];
            foreach ($classifys as $classify) {
                if ($classify->pid == $curPid) {
                    // 检查有没有子树
                    if ($hasSubTree($classifys, $classify->classify_id)) {
                        $classify->sub_nodes = $buildTree($classifys, $classify->classify_id);
                    }
                    $tree[] = $classify;
                }
            }
            return $tree;
        };

        return $buildTree($classifys);
    }

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
    public function loadClassifysByType(string $classifyType)
    {
        return $this->pdo->select()->from($this->getTable())->where('classify_type', '=', $classifyType)
                ->orderBy([['sort_index', 'ASC'], ['classify_id', 'DESC']])
                ->query()->fetchAll('\nPub\SPI\Persistence\Classify\Classify');
    }

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
    /*public function loadContents($classifyId, $curPage = 1, $pageSize = 20)
    {
        $queryAdapter = new QueryAdapter($this->pdo);
        $queryAdapter->from = new Criterion\Table(ClassifyContent::tableName());
        $queryAdapter->filter = new Criterion\Field('classify_id', Criterion\Operator::EQ, $classifyId);
        $queryAdapter->sortClauses = [
            new Criterion\SortClause('sort_index', Criterion\SortClause::SORT_ASC),
            new Criterion\SortClause('content_id', Criterion\SortClause::SORT_DESC),
        ];

        $paginator = new Paginator($queryAdapter, '\nPub\SPI\Persistence\Classify\ClassifyContent');
        $paginator->setPageParams($curPage, $pageSize);

        return $paginator;
    }*/

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
    /*public function loadContentsPaginator(QueryAdapter $queryAdapter, $curPage = 1, $pageSize = 20)
    {
        if (null === $queryAdapter->from)
            $queryAdapter->from = new Criterion\Table(ClassifyContent::tableName());
        if (!$queryAdapter->sortClauses)
            $queryAdapter->sortClauses = [
                new Criterion\SortClause('sort_index', Criterion\SortClause::SORT_ASC),
                new Criterion\SortClause('content_id', Criterion\SortClause::SORT_DESC),
            ];

        $paginator = new Paginator($queryAdapter);
        $paginator->setPageParams($curPage, $pageSize);

        return $paginator;
    }*/

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
    public function changeParentNode(Classify $classify, Classify $newParent)
    {
        $in = $this->pdo->inTransaction();
        $in || $this->pdo->beginTransaction();

        $fields = [
            'pid'       => $newParent->getClassifyId(),
            'root_path' => rtrim($newParent->getRootPath(), '/') . '/' . $newParent->getClassifyId(),
        ];
        if (!$this->pdo->update($this->getTable())->set($fields)->where('classify_id', '=', $classify->getClassifyId())->exec()) {
            $this->pdo->rollBack();
            return false;
        }

        $childNewRootPath = rtrim($fields['root_path'], '/') . '/' . $classify->getClassifyId();
        $childOldRootPath = rtrim($classify->getRootPath(), '/') . '/' . $classify->getClassifyId();
        if (false === $this->pdo->update($this->getTable())->set(['root_path' => "REPLACE(`root_path`, '{$childOldRootPath}', '{$childNewRootPath}')"])
                ->where('root_path', 'like', $childOldRootPath . '%')->exec()) {
            $this->pdo->rollBack();
            return false;
        }

        $in || $this->pdo->commit();
        return true;
    }

    /**
     * Return the number of contents in classify by given.
     *
     * @param int $classifyId
     * @return int
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    /*public function hasContents($classifyId)
    {
        return $this->pdo->select()->from(ClassifyContent::tableName())
                ->where(['classify_id', '=', $classifyId])
                ->count();
    }*/

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
        return $this->pdo->select()->from($this->getTable())
                ->where('pid', '=', $classifyId)
                ->count();
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
        $where = null === $classifyId ? ['pid', '=', 0] : (is_array($classifyId) ? _in_('pid', $classifyId) : ['pid', '=', $classifyId]);

        $query = $this->pdo->select()->from($this->getTable())->where($where)
                ->orderBy([['sort_index', 'ASC'], ['classify_id', 'DESC']])->query();

        return $query->fetchAll('\nPub\SPI\Persistence\Classify\Classify');
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
        $where = _or_(['root_path', 'LIKE', '%/' . $classifyId], ['root_path', 'LIKE', '%/' . $classifyId . '/%']);
        $query = $this->pdo->select()->from($this->getTable())->where($where)
                ->orderBy([['sort_index', 'ASC'], ['classify_id', 'DESC']])
                ->query();

        return $query->fetchAll('\nPub\SPI\Persistence\Classify\Classify');
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
        $rootpaths = explode('/', trim($classify->getRootPath(), '/'));
        $query = $this->pdo->select()->from($this->getTable())->where(_in_('classify_id', $rootpaths))
                ->query();

        $ret = [];
        foreach ($query->fetchAll('\nPub\SPI\Persistence\Classify\Classify') as $f) {
            $ret[$f->classify_id] = $f;
        }

        return $ret;
    }

    /**
     * Build the options of select
     *
     * @param array $classifyTree
     * @param int $select
     * @param int $level
     * @return string
     */
    public function buildOptions(array $classifyTree, $select = null, $level = 0)
    {
        $space = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $level);
        $dl = $level;
        $level++;
        $option = '';
        foreach ($classifyTree as $r) {
            $selected = null !== $select && $r->classify_id == $select ? ' selected="selected"' : '';
            $option .= '<option value="' . $r->classify_id . '"' . $selected . ' data-level="' . $dl . '">' . $space . $r->classify_name . '</option>';
            if (isset($r->sub_nodes) && !empty($r->sub_nodes)) {
                $option .= $this->buildOptions($r->sub_nodes, $select, $level);
            }
        }

        return $option;
    }

    /**
     * set database table
     *
     * @param string $table
     */
    public function setTable(string $table)
    {
        $this->table = $table;
    }

    /**
     * get database table
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table ?: Classify::tableName();
    }
}

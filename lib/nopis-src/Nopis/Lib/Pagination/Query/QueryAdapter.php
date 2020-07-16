<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Pagination\Query;

use Nopis\Lib\Pagination\QueryAdapterInterface;
use Nopis\Lib\Database\DBInterface;

/**
 * Description of Query
 *
 * @author wangbin
 */
class QueryAdapter implements QueryAdapterInterface
{

    /**
     * @var \Nopis\Lib\Database\DBInterface
     */
    private $db;

    /**
     * @var string
     */
    private $returnEntity = null;

    /**
     *
     * @var \Nopis\Lib\Pagination\Query\Criterion\SelectInterface
     */
    public $select;

    /**
     *
     * @var \Nopis\Lib\Pagination\Query\Criterion\Table
     */
    public $from;

    /**
     * The Query filter
     *
     * @var \Nopis\Lib\Pagination\Query\Criterion\FilterInterface
     */
    public $filter;

    /**
     * GROUP BY
     *
     * @var \Nopis\Lib\Pagination\Query\Criterion\GroupBy
     */
    public $groupBy;

    /**
     * Query sorting clauses
     *
     * @var \Nopis\Lib\Pagination\Query\Criterion\SortClause[]
     */
    public $sortClauses = [];

    /**
     *
     * @param \Nopis\Lib\Database\DBInterface $db
     */
    public function __construct(DBInterface $db)
    {
        $this->db = $db;
        $this->returnEntity = null;
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbResults()
    {
        if ($this->groupBy) {
            $distinct = 'COUNT(DISTINCT ' . implode(',', $this->groupBy->getCriterion()) . ')';
            $stmt = $this->db->select($distinct)->from($this->from->getCriterion());
            if ($this->filter instanceof Criterion\FilterInterface) {
                $stmt = $stmt->where($this->filter->getCriterion());
            }
            return $stmt->query()->fetchColumn();
        }
        $stmt = $this->db->select()->from($this->from->getCriterion());
        if ($this->filter instanceof Criterion\FilterInterface) {
            $stmt = $stmt->where($this->filter->getCriterion());
        }

        return $stmt->count();
    }

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return array|\Traversable The slice.
     */
    public function getSlice($offset, $length)
    {
        $sortClauses = [];
        foreach ($this->sortClauses as $sortClause) {
            $sortClauses[] = $sortClause->getCriterion();
        }

        $stmt = $this->db->select(null === $this->select ? null : $this->select->getCriterion())->from($this->from->getCriterion());
        if ($this->filter instanceof Criterion\FilterInterface) {
            $stmt = $stmt->where($this->filter->getCriterion());
        }
        if ($this->groupBy) {
            $stmt = $stmt->groupBy($this->groupBy->getCriterion());
        }

        return $stmt->orderBy($sortClauses)->limit($offset, $length)
                ->query()->fetchAll($this->returnEntity);
    }

    /**
     * Set the return entity
     *
     * @param string $entity
     */
    public function setReturnEntity($entity)
    {
        $this->returnEntity = $entity;
    }

    /**
     * Check select if be set.
     *
     * @return boolean
     */
    public function selectionIsNull()
    {
        return null === $this->select;
    }
}

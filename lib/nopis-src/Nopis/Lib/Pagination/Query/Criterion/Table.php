<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Pagination\Query\Criterion;

/**
 * Description of Table
 *
 * @author wangbin
 */
class Table implements FromInterface
{

    /**
     * @var string
     */
    private $table;

    /**
     * Constructor.
     *
     * @param string $table    数据表名
     * @param string $alias    数据表别名
     * @param \Nopis\Lib\Pagination\Query\Criterion\JoinInterface $joins    可变参数[JOIN 语句]，如果可变参数 $joins 的个数不为零，则第二个参数 $tableAlias 也必须给定值
     * @throws \InvalidArgumentException
     */
    public function __construct($table, $alias = null, JoinInterface ...$joins)
    {
        if (null == $alias && count($joins) > 0) {
            throw new \InvalidArgumentException(
                sprintf('Class "%s" if use parameter "$join", then parameter "$tableAlias" must be given', get_class($this))
            );
        }

        $this->table = $table;

        if (null != $alias) {
            $this->table .= ' AS ' . $alias;
        }

        foreach ($joins as $join) {
            if ($join instanceof JoinInterface) {
                $this->table .= $join;
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Argument "$joins" of class "%s" expect an Object of class who implements JoinInterface, %s given', get_class($this), get_class($join))
                );
            }
        }
    }

    /**
     * Return the query criterion.
     *
     * @return string
     */
    public function getCriterion()
    {
        return $this->table;
    }
}

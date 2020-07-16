<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Database\Query;

/**
 * @author Wangbin
 */
class Tables
{
    /**
     * @var array
     */
    protected $tables = [];

    /**
     * @var array
     */
    protected $alias;

    /**
     * Constructor.
     *
     * @param array $tables  例：[tbl_name1, tbl_name2, ...] 或 [[tbl_name1, tbl_as1], [tbl_name2, tbl_as2], ...]
     */
    public function __construct(array $tables)
    {
        foreach ($tables as $table) {
            if (is_array($table)) {
                $tblName = trim($table[0]);
                $this->alias[$tblName][] = trim($table[1]);
                $this->tables[] = $tblName;
            } else {
                $this->tables[] = trim($table);
            }
        }
    }

    /**
     * Return database table alias name.
     *
     * @param string $tblName
     * @return null|array
     */
    protected function getAlias($tblName)
    {
        return isset($this->alias[$tblName]) ? $this->alias[$tblName] : null;
    }

    public function __toString()
    {
        $ret = [];
        foreach ($this->tables as $table) {
            $alias = $this->getAlias($table);
            $ret[] = $table . ($alias ? ' AS ' . $alias[0] : '');
        }
        return implode(', ', $ret);
    }
}

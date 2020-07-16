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
class Join extends Tables
{
    /**
     * @var mixed
     */
    private $on;

    /**
     * @var string
     */
    private $joinType;

    /**
     * Constructor.
     *
     * @param string $table
     * @param array|string $on
     * @param string|null $alias
     * @param string|null $joinType
     */
    public function __construct($table, $on, $alias = null, $joinType = null)
    {
        $table = $alias ? [[$table, $alias]] : [$table];
        parent::__construct($table);

        $this->on = $on;
        $this->joinType = strtoupper(trim($joinType));
    }

    public function __toString()
    {
        $on = [];
        if (is_string($this->on)) {
            $on[] = $this->on;
        } else {
            if (is_array($this->on[0])) {
                foreach ($this->on as $row) {
                    $on[] = $row[0] . ' = ' . $row[1];
                }
            } else {
                $on[] = $this->on[0] . ' = ' . $this->on[1];
            }
        }
        return $this->tables ? ' ' . ($this->joinType ?: '') . ' JOIN ' . parent::__toString() . ' ON ' . implode(' AND ', $on) : '';
    }
}

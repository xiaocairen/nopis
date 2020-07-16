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
class From extends Tables
{

    /**
     * SQL的from语句
     *
     * @param string|array $tables
     * @param string $alias
     */
    public function __construct($tables, $alias = null)
    {
        if (is_string($tables) && null !== $alias) {
            $tableParams = [[$tables, $alias]];
        } else {
            $tableParams = (array) $tables;
        }
        parent::__construct((array) $tableParams);
    }

    public function __toString()
    {
        return ' FROM ' . parent::__toString();
    }
}

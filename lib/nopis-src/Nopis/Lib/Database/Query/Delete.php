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
class Delete extends Tables
{

    /**
     * Constructor.
     *
     * @param string|array $tables
     * @param boolean $isAlias
     */
    public function __construct($tables = null, $isAlias = false)
    {
        $tables = null === $tables ? [] : (array) $tables ;
        $isAlias ? $this->tables = $tables : parent::__construct($tables);
    }

    public function __toString()
    {
        return 'DELETE ' . parent::__toString();
    }
}

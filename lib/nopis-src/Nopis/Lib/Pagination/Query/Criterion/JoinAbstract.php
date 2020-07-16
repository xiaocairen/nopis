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

use nPub\Core\Base\Exceptions\InvalidArgumentValue;

/**
 * Description of JoinAbstract
 *
 * @author wangbin
 */
abstract class JoinAbstract implements JoinInterface
{

    /**
     * @var string
     */
    protected $joinQuery = null;

    /**
     * Constructor.
     *
     * @param string $table
     * @param string $alias
     * @param string $on
     * @throws \nPub\Core\Base\Exceptions\InvalidArgumentValue
     */
    public function __construct($table, $alias, $on)
    {
        if (!is_string($table) || empty($table))
            throw new InvalidArgumentValue('table', $table);

        if (!is_string($alias) || empty($alias))
            throw new InvalidArgumentValue("alias", $alias);

        $this->joinQuery = ' ' . $table . ' AS ' . $alias . ' ' . ($on ? ' ON ' . $on : '');
    }

    abstract public function __toString();
}

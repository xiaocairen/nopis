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
class GroupBy
{
    /**
     * @var array
     */
    private $groupby;

    /**
     * Constructor.
     *
     * @param array $groupby
     */
    public function __construct(array $groupby)
    {
        $this->groupby = $groupby;
    }

    public function __toString()
    {
        return $this->groupby ? ' GROUP BY ' . implode(', ', $this->groupby) : '';
    }
}

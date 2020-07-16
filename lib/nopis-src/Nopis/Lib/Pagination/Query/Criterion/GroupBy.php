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
 * Description of GroupBy
 *
 * @author wangbin_hn
 */
class GroupBy
{
    /**
     * @var array
     */
    protected $targets = [];

    /**
     * Constructor.
     *
     * @param string $target
     * @param string $sortType
     */
    public function __construct(string ...$targets)
    {
        if (empty($targets))
            throw new \InvalidArgumentException('Argument "targets" is empty in class ' . get_class($this));

        $this->targets = $targets;
    }

    /**
     * @return array
     */
    public function getCriterion()
    {
        return $this->targets;
    }
}

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
 * Description of LogicalOperator
 *
 * @author wangbin
 */
abstract class LogicalOperator implements FilterInterface
{
    /**
     * The set of criteria combined by the logical operator
     * @var \Nopis\Lib\Pagination\Query\Criterion\FilterInterface[]
     */
    protected $filters = [];

    /**
     * Creates a Logic operation with the given criteria
     *
     * @param FilterInterface $filters 可变参数，继承自FilterInterface接口的对象的一个实例，
     *                                  如果参数是LogicalNull的一个实例，则忽略该参数
     *
     * @throws \InvalidArgumentException
     */
    public function __construct( FilterInterface ...$filters )
    {
        foreach ( $filters as $filter ) {
            if ( $filter instanceof LogicalNull )
                continue;

            $this->filters[] = $filter;
        }
    }
}

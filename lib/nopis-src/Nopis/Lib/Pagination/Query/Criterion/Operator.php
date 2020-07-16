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
 * Operators struct
 *
 * @author wangbin
 */
abstract class Operator
{
    const EQ       = '=';
    const NOT_EQ   = '<>';
    const GT       = '>';
    const GTE      = '>=';
    const LT       = '<';
    const LTE      = '<=';
    const IS       = 'IS';
    const IS_NOT   = 'IS NOT';
    const IN       = 'IN';
    const NOT_IN   = 'NOT IN';
    const BETWEEN  = 'BETWEEN';
    const LIKE     = 'LIKE';
    // const CONTAINS = 'CONTAINS';
}

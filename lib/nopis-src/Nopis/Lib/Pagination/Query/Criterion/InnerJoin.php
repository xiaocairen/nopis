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
 * Description of InnerJoin
 *
 * @author wangbin
 */
class InnerJoin extends JoinAbstract
{
    public function __toString()
    {
        return ' JOIN ' . $this->joinQuery;
    }
}

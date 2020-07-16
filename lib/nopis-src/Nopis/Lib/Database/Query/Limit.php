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
class Limit
{

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int|null
     */
    private $rowCount;

    /**
     * Constructor.
     *
     * @param int $offset
     * @param int $rowCount
     */
    public function __construct($offset, $rowCount)
    {
        $this->offset  = $offset;
        $this->rowCount = $rowCount;
    }

    public function __toString()
    {
        return ' LIMIT ' . $this->offset . ', ' . $this->rowCount;
    }

}

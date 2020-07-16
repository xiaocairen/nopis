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
class OnDuplicateKeyUpdate
{
    /**
     * @var array
     */
    private $set;

    /**
     * Constructor.
     *
     * @param array $set
     */
    public function __construct(array $set)
    {
        foreach ($set as $row) {
            if (is_array($row)) {
                if (2 !== count($row)) {
                    throw new \InvalidArgumentException(
                        sprintf('Function "%s" argument is invalid', __METHOD__)
                    );
                }

                $this->set[] = $row[0] . ' = ' . $row[1];
            } else {
                $this->set[] = $row . ' = VALUES(' . $row . ')';
            }
        }
    }

    public function __toString()
    {
        return $this->set ? ' ON DUPLICATE KEY UPDATE ' . implode(', ', $this->set) : '';
    }
}

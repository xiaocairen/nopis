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
class OrderBy
{
    /**
     * @var array
     */
    private $orders;

    public function __construct(array $orders)
    {
        if ($this->isOnlyOneOrder($orders)) {
            $orders[1] = strtoupper(trim($orders[1]));
        } else {
            foreach ($orders as & $row) {
                if (!is_array($row)) {
                    $row = [$row, 'ASC'];
                } elseif (!isset($row[1]) || !is_string($row[1]) || !in_array(strtoupper($row[1]), ['DESC', 'ASC'])) {
                    throw new \InvalidArgumentException(
                        sprintf('Function "%s" argument is invalid', __METHOD__)
                    );
                } else {
                    $row = [$row[0], strtoupper($row[1])];
                }
            }
        }
        $this->orders = $orders;
    }

    public function __toString()
    {
        if (!$this->orders) {
            return '';
        }

        $orders = [];
        if ($this->isOnlyOneOrder($this->orders)) {
            $orders[] = $this->orders[0] . ' ' . $this->orders[1];
        } else {
            foreach ($this->orders as $order) {
                $orders[] = $order[0] . ' ' . $order[1];
            }
        }

        return ' ORDER BY ' . implode(', ', $orders);
    }

    private function isOnlyOneOrder($orders)
    {
        return 2 === count($orders) && is_string($orders[1]) && in_array(strtoupper(trim($orders[1])), ['DESC', 'ASC']);
    }
}

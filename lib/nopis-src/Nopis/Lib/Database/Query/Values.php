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

use Nopis\Lib\Database\DBInterface;
use Nopis\Lib\Database\Params;

/**
 * @author Wangbin
 */
class Values
{
    /**
     * @var array
     */
    private $values;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var string
     */
    private $placeholderPrefix = 'val_';

    /**
     * @var \Nopis\Lib\Database\DB
     */
    private $query;

    /**
     * @var boolean
     */
    private $useBound;

    /**
     * Constructor.
     *
     * @param array $values     数组要求如下：
     * <ul>
     *  <li>一维数组 [ field_1 => value_1, field_2 => value_2, ... ]，则SQL语句一次插入一条数据。</li>
     *  <li>二维数组 [ [ field_1 => value_1, field_2 => value_2, ... ], [...], ... ]，则SQL语句一次插入N条数据</li>
     *  <li>数组的键必须是字段名</li>
     * </ul>
     *
     * @param \Nopis\Lib\Database\DB $query
     */
    public function __construct(array $values, DBInterface $query)
    {
        if (!is_assoc($values)) {   // 二维数组
            foreach ($values as $key => $row) {
                if (!is_array($row) || !is_assoc($row)) {
                    throw new \InvalidArgumentException(
                        sprintf('Function "%s" argument 1 expect an assoc sub array, an enum array given', __METHOD__)
                    );
                }
                ksort($values[$key]);
            }

            $this->values = $values;
            $this->fields = array_keys(current($values));
            $this->query = $query;
            $this->useBound = false;
        } else {
            $this->query = $query;

            $params = [];
            foreach ($values as $field => $value) {
                $placeholder = ':' . $this->placeholderPrefix . $field;
                $this->values[] = $placeholder;
                $params[$placeholder] = $value;
            }

            $this->fields = array_keys($values);
            $this->useBound = true;

            $newParams = new Params();
            $newParams->setParams($params);
            $this->query->setQueryParams($newParams);
        }
    }

    public function __toString()
    {
        if (!$this->values) {
            return '';
        }

        $values = [];
        if ($this->useBound) {
            $values[] = '(' . implode(', ', $this->values) . ')';
        } else {
            foreach ($this->values as $row) {
                $row = array_map([$this->query, 'quote'], $row);
                $values[] = '(' . implode(', ', $row) . ')';
            }
        }

        return ' (' . implode(', ', $this->fields) . ') VALUES ' . implode(', ', $values);
    }
}

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
class Where
{

    /**
     * @var string
     */
    private $placeholderPrefix = 'whe_';

    /**
     * @var \Nopis\Lib\Database\DB
     */
    private $query;

    /**
     * @var string
     */
    private $where;

    /**
     * Constructor.
     *
     * @param array|\Nopis\Lib\Database\Params $where
     * @param \Nopis\Lib\Database\DB $query
     */
    public function __construct($where, DBInterface $query)
    {
        check_query_arg($where, __METHOD__);

        $this->query = $query;
        $params = [];
        if (is_array($where)) {   // [$field, '>', $value] or [$field, 'IS NOT', 'NULL']
            $where[1] = strtoupper(trim($where[1]));
            if ($where[1] === 'IS' || $where[1] === 'IS NOT') {
                $this->where = $where[0] . ' ' . $where[1] . ' ' . strtoupper(trim($where[2]));
            } else {
                $placeholder = ':' . $this->placeholderPrefix . preg_replace('/[^a-zA-Z0-9_]/', '_', $where[0]);
                $params[$placeholder] = $where[2];
                $this->where = $where[0] . ' ' . $where[1] . ' ' . $placeholder;
            }
        } elseif ($where instanceof Params) {
            $params = $where->getParams();
            $this->where = $where->getQuery();
        } elseif (null == $where) {
            $this->where = '1';
        }

        if ($params) {
            $newParams = new Params();
            $newParams->setParams($params);
            $this->query->setQueryParams($newParams);
        }
    }

    public function __toString()
    {
        return $this->where ? ' WHERE ' . $this->where : '';
    }

}

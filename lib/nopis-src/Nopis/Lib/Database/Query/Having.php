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
class Having
{
    /**
     * @var string
     */
    private $placeholderPrefix = 'hav_';

    /**
     * @var \Nopis\Lib\Database\DB
     */
    private $query;

    /**
     * @var string
     */
    private $having;

    /**
     * Constructor.
     *
     * @param array|\Nopis\Lib\Database\Params $having
     * @param \Nopis\Lib\Database\DB $query
     */
    public function __construct($having, DBInterface $query)
    {
        check_query_arg($having, __METHOD__);

        $this->query = $query;
        $params = [];
        if (is_array($having)) {   // [$field, '>', $value] or [$field, 'IS NOT', 'NULL']
            $having[1] = strtoupper(trim($having[1]));

            if ($having[1] === 'IS' || $having[1] === 'IS NOT') {
                $this->having = $having[0] . ' ' . $having[1] . ' ' . strtoupper(trim($having[2]));
            } else {
                $placeholder = ':' . $this->placeholderPrefix . $having[0];
                $params[$placeholder] = $having[2];
                $this->having = $having[0] . ' ' . $having[1] . ' ' . $placeholder;
            }
        } elseif ($having instanceof Params) {
            $params = $having->getParams();
            $this->having = $having->getQuery();
        }

        if ($params) {
            $newParams = new Params();
            $newParams->setParams($params);
            $this->query->setQueryParams($newParams);
        }
    }

    public function __toString()
    {
        return $this->having ? ' HAVING ' . $this->having : '';
    }
}

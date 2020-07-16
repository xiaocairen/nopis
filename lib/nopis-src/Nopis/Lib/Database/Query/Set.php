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
class Set
{
    /**
     * @var array
     */
    private $set;

    /**
     * @var string
     */
    private $placeholderPrefix = 'set_';

    /**
     * @var \Nopis\Lib\Database\DB
     */
    private $query;

    /**
     * Constructor.
     *
     * @param array $values [field_name1 => field_value1, field_name2 => field_value2]
     * @param \Nopis\Lib\Database\AbstractQuery $query
     */
    public function __construct(array $values, DBInterface $query)
    {
        if (!is_assoc($values)) {
            throw new \InvalidArgumentException(
                sprintf('Function "%s" argument 1 expect an assoc array, an enum array given', __METHOD__)
            );
        }

        $this->query = $query;

        $dbFuncs = FuncCollection::getFuncs();

        $params = [];
        foreach ($values as $field => $value) {
            if (preg_match('/^\s*' . $field . '\s*[+-]\s*\d+\s*$/i', $value)
                    || (preg_match('/^([a-z0-9_]+)\(.*\)$/i', $value, $m) && in_array(strtoupper($m[1]), $dbFuncs))) {
                // for field_name = field_name + 1 or field_name = field_name - 2
                // or  field_name = DB_FUNC(...)
                $this->set[$field] = $value;
            } else {
                $placeholder = ':' . $this->placeholderPrefix . $field;
                $this->set[$field] = $placeholder;
                $params[$placeholder] = $value;
            }
        }

        $newParams = new Params();
        $newParams->setParams($params);
        $this->query->setQueryParams($newParams);
    }

    public function __toString()
    {
        if (!$this->set) {
            return '';
        }

        $set = [];
        foreach ($this->set as $field => $placeholder) {
            $set[] = $field . ' = ' . $placeholder;
        }
        return ' SET ' . implode(', ', $set);
    }
}

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
class Select
{
    /**
     * @var array
     */
    private $fields;

    /**
     * @var boolean
     */
    private $distinct;

    /**
     * Constructor.
     *
     * @param array $fields
     * @param boolean $distinct
     */
    public function __construct($fields = null, $distinct = false)
    {
        if (is_array($fields) && is_assoc($fields)) {
            foreach ($fields as $tbl_alias => $tbl_field) {
                if (is_array($tbl_field)) {
                    foreach ($tbl_field as $field) {
                        $this->fields[] = $tbl_alias . '.' . $field;
                    }
                } else {
                    $this->fields[] = $tbl_alias . '.' . $tbl_field;
                }
            }
        } else {
            $this->fields = null === $fields ? [] : (is_array($fields) ? $fields : array($fields));
        }

        $this->distinct = (boolean) $distinct;
    }

    public function __toString()
    {
        return 'SELECT ' . ($this->distinct ? 'DISTINCT ' : '') . ($this->fields ? implode(',', $this->fields) : '*');
    }
}

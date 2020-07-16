<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Utils;

/**
 * Description of _stdClass
 *
 * @author wangbin
 */
class _stdClass extends \stdClass
{
    public function __construct(array $properties = [])
    {
        foreach ($properties as $k => $v) {
            preg_match('/^[_a-z]/i', $k) && $v !== null && $this->$k = $v;
        }
    }
}

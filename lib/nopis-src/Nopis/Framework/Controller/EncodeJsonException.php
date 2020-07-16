<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Controller;

use Exception;

/**
 * Description of EncodeJsonException
 *
 * @author wangbin
 */
class EncodeJsonException extends Exception
{
    /**
     * @param array $param
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct(array $param, int $code = 0, Exception $previous = null)
    {
        parent::__construct('json_encode(' . var_export($param, true) . ') occured error', $code, $previous);
    }
}

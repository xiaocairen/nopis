<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Database\Exceptions;

/**
 * Description of PDOSErrorException
 *
 * @author wangbin
 */
class PDOSErrorException extends QueryErrorException
{
    /**
     * @param \PDOStatement $pdos
     */
    public function __construct(\PDOStatement $pdos)
    {
        $errorInfo = $pdos->errorInfo();

        parent::__construct(
            sprintf('SQLSTATE[%s]: %s', $errorInfo[0], $errorInfo[2])
        );
    }
}

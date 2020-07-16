<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\Base\Exceptions;

use InvalidArgumentException as BaseInvalidArgumentException;
use Exception;

/**
 * Description of InvalidArgumentException
 *
 * @author wangbin
 */
class InvalidArgumentException extends BaseInvalidArgumentException
{
    /**
     * Generates: "Argument '{$argumentName}' is invalid: {$whatIsWrong}"
     *
     * @param string $argumentName
     * @param string $whatIsWrong
     * @param \Exception|null $previous
     */
    public function __construct( $argumentName, $whatIsWrong, Exception $previous = null )
    {
        parent::__construct(
            "Argument '{$argumentName}' is invalid: {$whatIsWrong}",
            0,
            $previous
        );
    }
}

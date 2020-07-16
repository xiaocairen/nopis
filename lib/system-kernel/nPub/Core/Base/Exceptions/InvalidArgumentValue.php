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

use Exception;

/**
 * Description of InvalidArgumentValue
 *
 * @author wangbin
 */
class InvalidArgumentValue extends InvalidArgumentException
{
    /**
     * Generates: "Argument '{$argumentName}' is invalid: '{$value}' is wrong value[ in class '{$className}']"
     *
     * @param string $argumentName
     * @param mixed $value
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct( $argumentName, $value, $className = null, Exception $previous = null )
    {
        $valueStr = is_string( $value ) ? $value : var_export( $value, true );

        parent::__construct(
            $argumentName,
            "'{$valueStr}' is wrong value" . ( $className ? " in class '{$className}'" : "" ),
            $previous
        );
    }
}

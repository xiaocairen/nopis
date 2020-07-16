<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Entity;

use Exception;

/**
 * This Exception is thrown on a write attempt in a read only property in a value object.
 *
 * @package nPub\API\Repository\Exceptions
 */
class PropertyReadOnlyException extends Exception
{
    /**
     * Generates: Property '{$propertyName}' is readonly[ on class '{$className}']
     *
     * @param string $propertyName
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct( $propertyName, $className = null, Exception $previous = null )
    {
        if ( $className === null )
            parent::__construct( "Property '{$propertyName}' is readonly", 0, $previous );
        else
            parent::__construct( "Property '{$propertyName}' is readonly on class '{$className}'", 0, $previous );
    }
}

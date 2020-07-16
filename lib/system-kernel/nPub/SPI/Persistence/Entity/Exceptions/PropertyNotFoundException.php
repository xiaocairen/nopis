<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Entity\Exceptions;

use Exception;

/**
 * This Exception is thrown if an accessed property in a value object was not found
 *
 * @package nPub\API\Repository\Exceptions
 */
class PropertyNotFoundException extends Exception
{
    /**
     * Generates: Property '{$propertyName}' not found
     *
     * @param string $propertyName
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct( $propertyName, $className = null, Exception $previous = null )
    {
        if ( $className === null )
            parent::__construct( "Property '{$propertyName}' not found", 0, $previous );
        else
            parent::__construct( "Property '{$propertyName}' not found on class '{$className}'", 0, $previous );
    }
}

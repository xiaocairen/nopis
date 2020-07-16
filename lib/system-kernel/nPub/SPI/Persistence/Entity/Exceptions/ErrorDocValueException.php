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

/**
 * Description of DefinitionException
 *
 * @author wangbin
 */
class ErrorDocValueException extends \Exception
{
    /**
     * @param string $docValue
     * @param string $docName
     * @param string $className
     */
    public function __construct( $docValue, $docName, $className )
    {
        parent::__construct(
            "Error document value '{$docValue}' is invalid at '@{$docName}' in class {$className}",
            0,
            null
        );
    }
}

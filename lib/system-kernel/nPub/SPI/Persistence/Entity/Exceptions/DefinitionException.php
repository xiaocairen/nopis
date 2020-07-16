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
class DefinitionException extends \Exception
{
    /**
     * Generates: "Definition in '{$className}' is invalid: {$whatIsWrong}"
     *
     * @param string $whatIsWrong
     * @param string $className
     * @param \Exception|null $previous
     */
    public function __construct( $whatIsWrong, $className, Exception $previous = null )
    {
        parent::__construct(
            "Definition in '{$className}' is invalid: {$whatIsWrong}",
            0,
            $previous
        );
    }
}

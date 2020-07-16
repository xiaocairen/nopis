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

use nPub\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use Exception;

/**
 * Description of NotFoundException
 *
 * @author wangbin
 */
class NotFoundException extends APINotFoundException
{
    /**
     * When $identifier is not null generates:<br />
     *  &nbsp;&nbsp;&nbsp; Could not find '{$what}' with identifier '{$identifier}' <br />
     *          or <br />
     *  &nbsp;&nbsp;&nbsp; The '{$what}' does not exist
     *
     * @param string $what
     * @param mixed $identifier
     * @param \Exception|null $previous
     */
    public function __construct( $what, $identifier = null, Exception $previous = null )
    {
        if ($identifier) {
            $identifierStr = is_string( $identifier ) ? $identifier : var_export( $identifier, true );
            parent::__construct(
                "Could not find '{$what}' with identifier '{$identifierStr}'",
                0,
                $previous
            );
        } else {
            parent::__construct(
                "The '{$what}' does not exist",
                0,
                $previous
            );
        }
    }
}

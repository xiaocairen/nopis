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
 * Description of UnverifiedException
 *
 * @author wb
 */
class UnverifiedException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message, 0, null);
    }
}

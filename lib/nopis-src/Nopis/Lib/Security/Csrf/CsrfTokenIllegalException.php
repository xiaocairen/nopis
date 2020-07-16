<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Security\Csrf;

/**
 * Description of CsrfTokenIllegalException
 *
 * @author wangbin
 */
class CsrfTokenIllegalException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Crsf token is illegal.', 0, null);
    }
}

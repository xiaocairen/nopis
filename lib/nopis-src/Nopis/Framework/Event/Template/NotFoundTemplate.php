<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Event\Template;

/**
 * Description of NotFoundTemplate
 *
 * @author wangbin
 */
class NotFoundTemplate extends \Exception
{
    public function __construct($template, $whatIsWrong = '')
    {
        parent::__construct(
            'Not found the template "' . $template . '"' . (!empty($whatIsWrong) ? ' [' . $whatIsWrong . '] ' : ''),
            0,
            null
        );
    }
}

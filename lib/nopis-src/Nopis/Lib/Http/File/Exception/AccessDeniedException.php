<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Http\File\Exception;

/**
 * Thrown when the access on a file was denied.
 */
class AccessDeniedException extends FileException
{
    /**
     * Constructor.
     *
     * @param string $path The path to the accessed file
     */
    public function __construct($path)
    {
        parent::__construct(sprintf('The file %s could not be accessed', $path));
    }
}

<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Http\File\MimeType;

use Nopis\Lib\Http\File\Exception\FileNotFoundException;
use Nopis\Lib\Http\File\Exception\AccessDeniedException;

/**
 * Guesses the mime type of a file
 *
 * @author Wangbin
 */
interface MimeTypeGuesserInterface
{
    /**
     * Guesses the mime type of the file with the given path.
     *
     * @param string $path The path to the file
     *
     * @return string         The mime type or NULL, if none could be guessed
     *
     * @throws FileNotFoundException  If the file does not exist
     * @throws AccessDeniedException  If the file could not be read
     */
    public function guess($path);
}

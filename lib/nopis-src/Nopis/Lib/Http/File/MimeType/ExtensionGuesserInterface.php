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

/**
 * Guesses the file extension corresponding to a given mime type
 */
interface ExtensionGuesserInterface
{
    /**
     * Makes a best guess for a file extension, given a mime type
     *
     * @param string $mimeType The mime type
     * @return string          The guessed extension or NULL, if none could be guessed
     */
    public function guess($mimeType);
}

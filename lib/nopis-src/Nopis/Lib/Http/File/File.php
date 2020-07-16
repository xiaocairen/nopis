<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Http\File;

use Nopis\Lib\Http\File\Exception\FileNotFoundException;
use Nopis\Lib\Http\File\MimeType\MimeTypeGuesser;
use Nopis\Lib\Http\File\MimeType\ExtensionGuesser;

/**
 * A file in the file system.
 *
 * @author Wangbin
 *
 * @api
 */
class File extends \SplFileInfo
{
    /**
     * Constructs a new file from the given path.
     *
     * @param string  $path      The path to the file
     * @param bool    $checkPath Whether to check the path or not
     *
     * @throws FileNotFoundException If the given path is not a file
     *
     * @api
     */
    public function __construct($path, $checkPath = true)
    {
        if ($checkPath && !is_file($path)) {
            throw new FileNotFoundException($path);
        }

        parent::__construct($path);
    }

    /**
     * Returns the extension based on the mime type.
     *
     * If the mime type is unknown, returns null.
     *
     * This method uses the mime type as guessed by getMimeType()
     * to guess the file extension.
     *
     * @return string|null The guessed extension or null if it cannot be guessed
     *
     * @api
     *
     * @see ExtensionGuesser
     * @see getMimeType()
     */
    public function guessExtension()
    {
        $type = $this->getMimeType();
        $guesser = ExtensionGuesser::getInstance();

        return $guesser->guess($type);
    }

    /**
     * Returns the mime type of the file.
     *
     * The mime type is guessed using a MimeTypeGuesser instance, which uses finfo(),
     * mime_content_type() and the system binary "file" (in this order), depending on
     * which of those are available.
     *
     * @return string|null The guessed mime type (i.e. "application/pdf")
     *
     * @see MimeTypeGuesser
     *
     * @api
     */
    public function getMimeType()
    {
        $guesser = MimeTypeGuesser::getInstance();

        return $guesser->guess($this->getPathname());
    }

    /**
     * Returns the extension of the file.
     *
     * \SplFileInfo::getExtension() is not available before PHP 5.3.6
     *
     * @return string The extension
     *
     * @api
     */
    public function getExtension()
    {
        return pathinfo($this->getBasename(), PATHINFO_EXTENSION);
    }

    /**
     * Returns locale independent base name of the given path with extension.
     *
     * @param string $name The new file name
     *
     * @return string containing
     */
    protected function getName($name)
    {
        $originalName = str_replace('\\', '/', $name);
        $pos = strrpos($originalName, '/');
        $originalName = false === $pos ? $originalName : substr($originalName, $pos + 1);

        return $originalName;
    }
}

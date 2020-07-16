<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\FileSystem;

class File
{

    /**
     * @var mixed
     */
    private $file;

    /**
     * @param string|array   $file    A comman file or a uploaded file via HTTP POST, like $_FILES['image']
     * @param boolean        $checkPath
     * @param boolean        $filter
     */
    public function __construct($file, $checkPath = true, $filter = true)
    {
        if ($checkPath && !is_array($file) && !is_file($file)) {
            throw new \Exception(sprintf('The file "%s" does not exist', $file));
        }
        $filter && !is_array($file) && $file = Dir::filter($file);
        $this->file = $file;
    }

    /**
     * Delete a file
     *
     * @return boolean
     * @throws \Exception
     */
    public function delete()
    {
        if (!is_file($this->file)) {
            return true;
        }

        $parent = dirname($this->file);
        if (!is_writable($parent)) {
            throw new \Exception("The directory '$parent' denied");
        }

        return unlink($this->file);
    }

    /**
     * Move uploaded file
     *
     * @param string  $dir           the dir save upload file
     * @param string  $destname      dest file name
     * @param boolean $imageonly     if true only allow upload image file
     * @param boolean $overwrite
     *
     * @return string|boolean
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function uploadFile($dir, $destname = null, $imageonly = true, $overwrite = false)
    {
        $originalName = $this->file['name'];
        $filesize = $this->file['size'];
        $tmpFile = $this->file['tmp_name'];
        $ext = strtolower(self::getExtension($originalName));
        $exts = ['gif', 'jpg', 'jpeg', 'png'];

        // check the uploaded file array
        if (!$originalName || !$filesize || !$tmpFile) {
            throw new \InvalidArgumentException('Not found the $_FILES array');
        }

        $dir = null === $destname ? rtrim($dir, '/') . '/' . date('ym') : $dir;
        if (!is_dir($dir) && !Dir::create($dir))
            throw new \InvalidArgumentException(sprintf('Directory "%s" not exists', $dir));
        if (!is_writable($dir))
            throw new \InvalidArgumentException(sprintf('Unable to write in the "%s" directory', $dir));

        if (($imageonly && !in_array($ext, $exts))
                || $filesize > self::getUploadMaxFilesize()
                || !is_uploaded_file($tmpFile)) {
            throw new \Exception(sprintf('The uploaded file "%s" is Illegal', $originalName));
        }
        if (in_array($ext, $exts)) {
            if (false === ($fileInfo = getimagesize($tmpFile))) {
                throw new \Exception(sprintf('The uploaded file "%s" is not image', $originalName));
            }
            $wxh = $fileInfo[0] . 'x' . $fileInfo[1];
        }

        if (!$destname) {
            $file = $dir . '/' . self::createRandomName($ext, null, (isset($wxh) ? '!' . $wxh : null));
        } else {
            $file = $dir . '/' . $destname . '.' . $ext;
            if (!$overwrite && file_exists($file)) {
                throw new \Exception('File \'' . $file . '\' is already exists');
            }
        }

        if (!@move_uploaded_file($tmpFile, $file)) {
            return false;
        }

        return $file;
    }

    /**
     * copy file
     *
     * @param string $source
     * @param string $dest
     * @param bool $overwrite
     * @return string
     */
    public function copyTo($dest, $overwrite = false)
    {
        $fileObj = new \SplFileInfo($this->file);
        if (!$fileObj->isFile()) {
            return false;
        }

        $destObj = new \SplFileInfo($dest);
        if ($destObj->isFile() && !$overwrite) {
            $dest = $destObj->getPath() . '/' . self::createRandomName(
                $destObj->getExtension(),
                $destObj->getBasename('.' . $destObj->getExtension())
            );
        }

        $dir = $destObj->getPath();
        if (!is_dir($dir) && !Dir::create($dir)) {
            throw new \Exception("Directory '$dir' does't exists");
        }

        if (!copy($this->file, $dest))
            return false;

        return $dest;
    }

    /**
     * move a file to the dir, and delete the old file
     *
     * @param string $dir
     * @param boolean $overwrite
     * @return string|boolean
     * @throws \Exception
     */
    public function moveTo($dir, $overwrite = false)
    {
        $fileInfo = new \SplFileInfo($this->file);
        if (!$fileInfo->isFile()) {
            return false;
        }

        if (!is_dir($dir) && !Dir::create($dir)) {
            throw new \Exception("Directory '$dir' does't exists");
        }

        $dest = rtrim($dir, '/') . '/' . $fileInfo->getFilename();
        if (false !== ($movedFile = $this->copyTo($dest, $overwrite))) {
            $this->delete();
        }

        return $movedFile;
    }

    /**
     * Get a rand filename
     *
     * @param string $ext       extension
     * @param string $name      filename
     * @param string $suffix    suffix
     * @param int $randomLen    random name length
     * @return string
     */
    public static function createRandomName(string $ext, string $name = '', string $suffix = '', int $randomLen = 3)
    {
        $my_rand = function($len) {
            if ($len <= 0)
                return '';
            $minAscii = 50;
            $maxAscii = 90;
            $noUse = [58, 59, 60, 61, 62, 63, 64, 73, 76, 79];
            $str = '';
            for ($i = 0; $i < $len;) {
                $randAscii = mt_rand($minAscii, $maxAscii);
                if (!in_array($randAscii, $noUse)) {
                    $str .= chr($randAscii);
                    $i++;
                }
            }
            return strtoupper($str);
        };

        $micro = explode('.', microtime(true));
        $filename = (!$name ? 'J' . date('ymdHis') : $name) . '-' . $micro[1];
        $randstr  = $my_rand($randomLen);

        return $filename . $randstr . ($suffix ?: '') . '.' . $ext;
    }

    /**
     * Returns the maximum size of an uploaded file as configured in php.ini
     *
     * @return int The maximum size of an uploaded file in bytes
     */
    public static function getUploadMaxFilesize()
    {
        static $max;

        if (null === $max) {
            $iniMax = strtolower(ini_get('upload_max_filesize'));

            if ('' === $iniMax) {
                return 2000000;
            }

            $max = ltrim($iniMax, '+');
            if (0 === strpos($max, '0x')) {
                $max = intval($max, 16);
            } elseif (0 === strpos($max, '0')) {
                $max = intval($max, 8);
            } else {
                $max = intval($max);
            }

            switch (substr($iniMax, -1)) {
                case 't': $max *= 1024;
                case 'g': $max *= 1024;
                case 'm': $max *= 1024;
                case 'k': $max *= 1024;
            }
        }

        return $max;
    }

    /**
     * Return file extension without dot
     *
     * @param string $file
     * @return string
     */
    public static function getExtension($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }
}

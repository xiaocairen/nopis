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

class Dir
{

    /**
     * Filte directory for different OS
     *
     * @param string $dir
     * @return string
     */
    public static function filter($dir)
    {
        $dir = trim($dir);
        if (empty($dir)) {
            return;
        }

        $dir = preg_replace('/[\/\\\\]+/', '/', $dir);
        if (false !== ($pos = strrpos($dir, './'))) {
            $realpart = realpath(substr($dir, 0, $pos + 2));
            $dir      = $realpart . '/' . substr($dir, $pos + 2);
        }

        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        $obd = ini_get('open_basedir');
        if (!empty($obd)) {
            $inBaseDir = false;
            $obdArr    = explode(PATH_SEPARATOR, $obd);
            foreach ($obdArr as $obds) {
                if (strpos($dir, $obds) === 0) {
                    $inBaseDir = true;
                    break;
                }
            }
            if (!$inBaseDir) {
                throw new \Exception("Dir '$dir' was rejected by server");
            }
        }

        return $dir;
    }

    /**
     * set the permission of file or folder, if can
     *
     * @param string $dir
     * @param octal $mode
     * @return boolean
     */
    public static function setPerms($dir, $mode = 0755)
    {
        $dir = self::filter($dir);

        if (is_dir($dir)) {
            $hd   = opendir($dir);
            while (($file = readdir($hd)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $fullpath = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($fullpath)) {
                    if (!self::setPerms($fullpath, $mode)) {
                        return false;
                    }
                } else {
                    if (!@chmod($fullpath, $mode)) {
                        return false;
                    }
                }
            }
            closedir($hd);

            if (!@chmod($dir, $mode)) {
                return false;
            }
        } else {
            if (!@chmod($dir, $mode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * recursive create folder
     *
     * @param string $dir
     * @param Octal  $mode
     * @return boolean
     */
    public static function create($dir, $mode = 0755)
    {
        $dir = Dir::filter($dir);

        if (is_dir($dir)) {
            return true;
        }

        $dirs     = explode(DIRECTORY_SEPARATOR, $dir);
        $mkfolder = '';
        for ($i = 0; isset($dirs[$i]); $i++) {
            $mkfolder .= $dirs[$i];
            if (!is_dir($mkfolder)) {
                @mkdir($mkfolder, $mode);
            }
            $mkfolder .= DIRECTORY_SEPARATOR;
        }

        if (is_dir($dir)) {
            return true;
        }

        return false;
    }

    /**
     * recursive delete folder
     *
     * @param <string> $path
     * @return <boolean>
     */
    public static function delete($path)
    {
        $path = Dir::filter($path);

        if (!is_dir($path)) {
            return true;
        }

        $parent = dirname($path);
        if (!is_writable($parent)) {
            trigger_error("the folder '$parent' denied");
        }

        $hd   = opendir($path);
        while (($file = readdir($hd)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $fullpath = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullpath)) {
                if (!self::delete($fullpath)) {
                    closedir($hd);
                    return false;
                }
            } else {
                if (!is_writable($fullpath)) {
                    if (!self::setPerms($fullpath, 0777)) {
                        closedir($hd);
                        return false;
                    }
                }
                if (!@unlink($fullpath)) {
                    closedir($hd);
                    return false;
                }
            }
        }
        closedir($hd);

        if (!@rmdir($path)) {
            return false;
        }

        return true;
    }

    /**
     * clear folder to empty
     *
     * @param <string> $path
     * @return <boolean>
     */
    public static function empties($path)
    {
        $path = Dir::filter($path);

        if (!is_dir($path)) {
            return true;
        }

        if (!is_writable($path)) {
            if (!self::setPerms($path, 0777)) {
                trigger_error("the folder '$path' denied");
            }
        }

        $hd   = opendir($path);
        while (($file = readdir($hd)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $fullpath = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullpath)) {
                if (!self::delete($fullpath)) {
                    closedir($hd);
                    return false;
                }
            } else {
                if (!is_writable($fullpath)) {
                    if (!self::setPerms($fullpath, 0777)) {
                        closedir($hd);
                        return false;
                    }
                }
                if (!@unlink($fullpath)) {
                    closedir($hd);
                    return false;
                }
            }
        }
        closedir($hd);

        return true;
    }

    /**
     * return all files which matchs pattern in path
     *
     * @param string $pattern
     * @param string $path
     * @param boolean $RE
     * @return array
     */
    public static function glob($pattern, $path, $RE = false)
    {
        $path = Dir::filter($path);
        if (!is_dir($path)) {
            trigger_error("PATH[$path] error");
            return false;
        }

        $dir = dir($path);
        if (!is_object($dir)) {
            trigger_error("can't open path[$path]");
            return false;
        }

        $return = array();
        while (false !== ($file   = $dir->read())) {
            if ($RE) {
                preg_match($pattern, $file) && $return[] = $file;
            } else {
                fnmatch($pattern, $file) && $return[] = $file;
            }
        }

        return $return;
    }

}

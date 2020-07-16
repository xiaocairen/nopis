<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Cache;

use Exception;
use RuntimeException;

/**
 * @author wangbin
 */
class FileCache implements NopisCacherInterface
{
    /**
     * /rootpath/to/nopis/pub/cache/cacher
     *
     * @var string
     */
    private $cacheDir;

    public function __construct()
    {
        $rootDir = implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, __DIR__), 0, -6));
        $this->cacheDir = $rootDir . DIRECTORY_SEPARATOR . 'pub' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'cached';
    }

    /**
     * Set cache
     *
     * @param string $key
     * @param string|array|LatteObject $data
     * @return boolean
     * @throws Exception
     */
    public function set($key, $data)
    {
        if (!is_string($key) || empty($key) || empty($data))
            throw new Exception('Params empty');

        $key = strtr($key, array('.' => '/'));
        $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . $key . '.php';

        $str = "<?php\n\n";
        switch (gettype($data)) {
            case 'string':
                $str .= "return <<<EOF\n__String__" . $data . "\nEOF;\n";
                break;
            case 'array':
                $str .= 'return ' . var_export($data, true);
                break;
            case 'object':
                $str .= "return <<<EOF\n__Object__" . serialize($data) . "\nEOF;\n";
                break;
            default:
                throw new Exception('Unsupported type ' . gettype($data));
        }

        try {
            $fileObj = new \SplFileObject($cacheFile, 'w');

            if (!$fileObj->fwrite($cacheStr)) {
                throw new Exception('Unable to write cache to cache file ' . $cacheFile);
            }
        } catch (RuntimeException $re) {
            throw new Exception('Open cache file Err ' . $re->getMessage());
        }

        return true;
    }

    /**
     * Get cache
     *
     * @param string $key
     * @return string|array|LatteObject
     * @throws Exception
     */
    public function get($key)
    {
        if (!is_string($key) || empty($key))
            throw new Exception('Params empty');

        $key = $this->resolveKey($key);
        $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . $key . '.php';
        if (!file_exists($cacheFile) || !is_readable($cacheFile)) {
            throw new Exception('Cache file is not exists or unreadable');
        }

        $cache = include $cacheFile;

        $data = null;
        switch (gettype($cache)) {
            case 'string':
                switch (substr($cache, 0, 10)) {
                    case '__String__':
                        $data = substr($cache, 10);
                        break;
                    case '__Object__':
                        $data = unserialize(substr($cache, 10));
                        if (false === $data) {
                            throw new Exception('Unable unserialize Object cache ' . substr($cache, 10));
                        }
                        break;
                    default:
                        throw new Exception('Unknown cache sign: ' . substr($cache, 0, 10));
                }
                $data = $cache;
                break;

            case 'array':
                $data = $cache;
                break;

            default:
                throw new Exception('Unknown cache type ' . gettype($cache));
        }

        return $data;
    }

    /**
     * Delete cache file
     *
     * @param string $key
     * @return boolean
     * @throws Exception
     */
    public function delete($key)
    {
        if (!is_string($key) || empty($key))
            throw new Exception('Params empty');

        $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . $this->resolveKey($key) . '.php';
        if (!file_exists($cacheFile))
            return true;

        return unlink($cacheFile);
    }

    /**
     * Translate cache key
     *
     * @param string $key
     * @return string
     */
    protected function resolveKey($key)
    {
        return strtolower(str_replace('.', DIRECTORY_SEPARATOR, $key));
    }
}

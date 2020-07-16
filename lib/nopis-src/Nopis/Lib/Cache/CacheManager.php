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

use Nopis\Lib\Database\DBCacheManagerInterface;

/**
 *
 * @author wangbin
 */
class CacheManager implements DBCacheManagerInterface
{
    /**
     *
     * @var \Nopis\Lib\Cache\NopisCacherInterface
     */
    private $cacher = null;

    public function __construct(NopisCacherInterface $cacher = null)
    {
        if (null === $cacher)
            $this->cacher = new FileCache();
        else
            $this->cacher = $cacher;
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
        return $this->cacher->set(trim($key), $data);
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
        return $this->cacher->get(trim($key));
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
        return $this->cacher->delete(trim($key));
    }
}

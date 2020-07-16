<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Database;

/**
 *
 * @author wangbin
 */
interface DBCacheManagerInterface
{
    /**
     * Set cache
     *
     * @param string $key
     * @param string|array|LatteObject $data
     * @return boolean
     * @throws Exception
     */
    public function set($key, $data);

    /**
     * Get cache
     *
     * @param string $key
     * @return string|array|LatteObject
     * @throws Exception
     */
    public function get($key);

    /**
     * Delete cache file
     *
     * @param string $key
     * @return boolean
     * @throws Exception
     */
    public function delete($key);
}

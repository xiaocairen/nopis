<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Config;

/**
 * @author wangbin
 */
interface ConfiguratorInterface
{

    /**
     * Check if the debug is opened
     *
     * @return boolean
     */
    public function debugIsOpen();

    /**
     * Return the web programme's root dir
     *
     * @return string
     */
    public function getRootDir();

    /**
     * Return app dir
     *
     * @return string
     */
    public function getPubDir();

    /**
     * Return lib dir
     *
     * @return string
     */
    public function getLibDir();

    /**
     * Return src dir
     *
     * @return string
     */
    public function getSrcDir();

    /**
     * Return web dir
     *
     * @return string
     */
    public function getWebDir();

    /**
     * Return defined configuration in /path/to/config.neon
     *
     * @param string $key
     * @return mixed
     */
    public function getConfig($key = null);

    /**
     * Return defined service in /path/to/service.neon
     *
     * @param string $key
     * @return array
     */
    public function getService($key = null);
}

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
class Configurator implements ConfiguratorInterface
{

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    private static $instance;

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $pubDir;

    /**
     * @var string
     */
    protected $libDir;

    /**
     * @var string
     */
    protected $srcDir;

    /**
     * @var string
     */
    protected $webDir;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $event;

    /**
     * @var array
     */
    protected $service;

    /**
     * the Configurator of application
     *
     * @param string   $configFile  the config files
     */
    private function __construct()
    {
        $loader = new Loader($this->getPubDir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.neon', $this);
        foreach ($loader as $k => $r) {
            if (property_exists($this, $k)) {
                $this->$k = $r;
            }
        }
    }

    private function __clone(){}

    /**
     * Return singleton object
     *
     * @return \Nopis\Lib\Config\ConfiguratorInterface
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Check if the debug is opened
     *
     * @return boolean
     */
    public function debugIsOpen()
    {
        if (null === $this->debug) {
            $this->debug = (boolean) $this->getConfig('framework.debug');
        }
        return $this->debug;
    }

    /**
     * Return the web programme's root dir
     *
     * @return string
     */
    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $this->rootDir = implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, __DIR__), 0, -5));
        }
        return $this->rootDir;
    }

    /**
     * Return app dir
     *
     * @return string
     */
    public function getPubDir()
    {
        if (null === $this->pubDir) {
            $this->pubDir = $this->getRootDir() . DIRECTORY_SEPARATOR . 'pub';
        }
        return $this->pubDir;
    }

    /**
     * Return lib dir
     *
     * @return string
     */
    public function getLibDir()
    {
        if (null === $this->libDir) {
            $this->libDir = $this->getRootDir() . DIRECTORY_SEPARATOR . 'lib';
        }
        return $this->libDir;
    }

    /**
     * Return the App's src dir
     *
     * @return string
     */
    public function getSrcDir()
    {
        if (null === $this->srcDir) {
            $this->srcDir = $this->getRootDir() . DIRECTORY_SEPARATOR . 'src';
        }
        return $this->srcDir;
    }

    /**
     * Return web dir
     *
     * @return string
     */
    public function getWebDir()
    {
        if (null === $this->webDir) {
            $this->webDir = $this->getRootDir() . DIRECTORY_SEPARATOR . 'web';
        }
        return $this->webDir;
    }

    /**
     * Return defined configuration in /path/to/config.neon
     *
     * @param string $key
     * @return mixed
     */
    public function getConfig($key = null)
    {
        if (null === $key)
            return $this->config;

        return $this->_get($key, 'config');
    }

    /**
     * Return defined service in /path/to/service.neon
     *
     * @param string $key
     * @return array
     */
    public function getService($key = null)
    {
        if (null === $key)
            return $this->service;

        return $this->_get($key, 'service');
    }

    /**
     * Return config by given $key
     *
     * @param string $key
     * @param string $id
     * @return mixed
     * @throws NotFoundConfigException
     */
    protected function _get($key, $id)
    {
        $keys   = $this->_str2array($key);
        $config = $this->$id;

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                throw new NotFoundConfigException("Configurator {$id}[{$key}] can't be found at '$k'");
            }
            $config = $config[$k];
        }

        return $config;
    }

    /**
     * translate string(eg. ' a.b.c.d ') to array
     *
     * @param string $key
     * @return array
     */
    protected function _str2array($key)
    {
        $keys = explode('.', $key);

        array_walk($keys, function(& $value) {
            $value = trim($value);
        });

        return array_values(array_filter($keys));
    }

}

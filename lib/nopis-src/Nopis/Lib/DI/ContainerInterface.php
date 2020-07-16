<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\DI;

use Nopis\Lib\Config\ConfiguratorInterface;

/**
 * @author wangbin
 */
interface ContainerInterface
{

    /**
     * Return singleton object
     *
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     *
     * @return \Nopis\Lib\DI\ContainerInterface
     */
    public static function getInstance(ConfiguratorInterface $configurator);

    /**
     * get an object by given Class Identifier $object
     *
     * @param string $classIdentifier
     * @param array  $args Temporary injection arguments at call-time, nonsupport recursive parameter
     *
     * @return Object
     */
    public function get(string $classIdentifier, array $args = []);

    /**
     * get a Shared object by given Class Identifier $object,
     * the object only be instantiated once
     *
     * @param string $classIdentifier
     *
     * @return Object
     */
    public function getShared(string $classIdentifier);

    /**
     * Get a singleton object
     *
     * @param string $classIdentifier
     * @param array $args
     *
     * @return Object
     */
    public function getSingleton(string $classIdentifier, array $args = []);

    /**
     * Set a singleton object
     *
     * @param string $serviceKey
     * @param type $service
     * @param bool $overwrite
     *
     * @return bool
     */
    public function set(string $serviceKey, $service, bool $overwrite = false);

    /**
     * register a class to table who will be instantiated after,
     *
     * @param string $classIdentifier
     */
    //public function set($classIdentifier);

    /**
     * register a class to table who will be instantiated after,
     * the object only be instantiated once
     *
     * @param string $classIdentifier
     */
    /*public function setShared($classIdentifier);

    public function getService($classIdentifier);

    public function setParameter(array $parameters);*/
}

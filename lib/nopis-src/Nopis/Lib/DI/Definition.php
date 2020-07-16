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
class Definition
{
    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    protected $configurator;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var array
     */
    protected $services = [];

    public function __construct(ConfiguratorInterface $configurator)
    {
        $this->configurator = $configurator;
        $this->parameters   = $this->configurator->getService('parameters');
        $this->services     = $this->configurator->getService('services');
    }

    /**
     * return the defined Parameter
     *
     * @param string $parameterName
     * @return string
     */
    public function getParameter($parameterName)
    {
        return isset($this->parameters[$parameterName]) ? $this->parameters[$parameterName] : null;
    }

    /**
     * return service definition
     *
     * @param string $serviceName
     * @return array
     */
    public function getService($serviceName)
    {
        return isset($this->services[$serviceName]) ? $this->services[$serviceName] : NULL;
    }

    /**
     * Return the var defined in config.neon
     *
     * @param string $key   eg. 'database.driver'
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->configurator->getConfig($key);
    }

}

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
class Container implements ContainerInterface
{

    /**
     * @var \Nopis\Lib\DI\ContainerInterface
     */
    private static $instance;

    /**
     * @var \Nopis\Lib\DI\Definition
     */
    protected $definition;

    /**
     * @var \Nopis\Lib\DI\BuilderInterface
     */
    protected $builder;

    /**
     * @var array
     */
    protected $services;

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     */
    private function __construct(ConfiguratorInterface $configurator)
    {
        if (null == $this->builder) {
            $this->definition = new Definition($configurator);
            $this->builder = new Builder($this->definition);
        }
    }

    private function __clone(){}

    /**
     * Return singleton object
     *
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     *
     * @return \Nopis\Lib\DI\ContainerInterface
     */
    public static function getInstance(ConfiguratorInterface $configurator = null)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self($configurator);
        }

        return self::$instance;
    }

    /**
     * Get an object by given the service name
     *
     * @param string $service
     * @param array  $args Temporary injection arguments at call-time, nonsupport recursive parameter
     *
     * @return Object
     */
    public function get(string $service, array $args = [])
    {
        return $args ? $this->builder->make($service, $args) : $this->getShared($service);
    }

    /**
     * get a Shared object by given the service name
     * the object only be instantiated once
     *
     * @param string $service
     *
     * @return Object
     */
    public function getShared(string $service)
    {
        if (!is_object($this->services[$service])) {
            $this->services[$service] = $this->builder->make($service);
        }

        return $this->services[$service];
    }

    /**
     * Get a singleton object
     *
     * @param string $service
     * @param array $args
     *
     * @return Object
     */
    public function getSingleton(string $service, array $args = [])
    {
        if (!is_object($this->services[$service])) {
            $this->services[$service] = $this->builder->makeSingleton($service, $args);
        }

        return $this->services[$service];
    }

    /**
     * Set a singleton object
     *
     * @param string $serviceKey
     * @param type $service
     * @param bool $overwrite
     *
     * @return bool
     */
    public function set(string $serviceKey, $service, bool $overwrite = false)
    {
        if (!is_object($service))
            return false;

        if (!isset($this->services[$serviceKey]) || $overwrite) {
            $this->services[$serviceKey] = $service;
            return true;
        }

        return false;
    }
}

/*
// 通过类名和参数，注册logger服务
$di->set('logger', array(
    'className' => 'Phalcon\Logger\Adapter\File',
    'arguments' => array(
        array(
            'type' => 'parameter',
            'value' => '../apps/logs/error.log'
        )
    )
));

// 改变logger服务的类名
$di->getService('logger')->setClassName('MyCustomLogger');

// 不用实例化就可以改变第一个参数值
$di->getService('logger')->setParameter(0, array(
    'type' => 'parameter',
    'value' => '../apps/logs/error.log'
));


// 构造子注入
$di->set('response', array(
    'className' => 'Phalcon\Http\Response'
));

$di->set('someComponent', array(
    'className' => 'SomeApp\SomeComponent',
    'arguments' => array(
        array('type' => 'service', 'name' => 'response'),
        array('type' => 'parameter', 'value' => true)
    )
));

// 设值注入
$di->set('response', array(
    'className' => 'Phalcon\Http\Response'
));

$di->set('someComponent', array(
    'className' => 'SomeApp\SomeComponent',
    'callMethod' => array(
        array(
            'method' => 'setResponse',
            'arguments' => array(
                array('type' => 'service', 'name' => 'response'),
            )
        ),
        array(
            'method' => 'setFlag',
            'arguments' => array(
                array('type' => 'parameter', 'value' => true)
            )
        )
    )
));

// 属性注入
$di->set('response', array(
    'className' => 'Phalcon\Http\Response'
));

$di->set('someComponent', array(
    'className' => 'SomeApp\SomeComponent',
    'properties' => array(
        array(
            'name' => 'response',
            'value' => array('type' => 'service', 'name' => 'response')
        ),
        array(
            'name' => 'someFlag',
            'value' => array('type' => 'parameter', 'value' => true)
        )
    )
));
*/

/*

parameters:
    Nopis.index.example.class: \IndexModule\DefaultController

services:
    # 构造子注入
    Nopis.index.example:
        className: @parameters:Nopis.index.example.class
        arguments:
            argument1: @class:\Nopis\Http\Response
            argument2: true

    # 设值注入
    Nopis.index1.example:
        className: @parameters:Nopis.index.example.class
        callMethods:
            - {
                methodName: setResponse,
                arguments:
                    argument1: @service:Nopis.index.example
                    argument2: 'wo zai henan'
            }
            - {
                methodName: setFlag,
                arguments:
                    argument1: {false, 'wo zai zhengzhou'}
            }

    # 属性注入
    Nopis.index2.example:
        className: @parameters:Nopis.index.example.class
        properties:
            response: @service:Nopis.index.example
            someFlag: false

*/
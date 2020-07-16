<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Console;

use Nopis\Lib\Console\Command\Getopt;
use Nopis\Lib\Config\ConfiguratorInterface;

/**
 * Description of OptCommend
 *
 * @author wangbin
 */
class OptCommand
{

    /**
     * @var string
     */
    public $createModule = 'create:module';

    /**
     * @var string
     */
    public $createEntity = 'create:entity';

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    public $configurator;

    /**
     * @var array
     */
    private $descriptions = [];

    public function __construct()
    {
        $this->setDescription($this->createModule, 'Please enter a name, Create Module with name like [Admin/IndexModule or BlogModule]');
        $this->setDescription($this->createEntity, 'Please enter the config file path like [--config=/path/to/generator_config.neon]');
    }

    public function dispatch(Getopt $getOpt, ConfiguratorInterface $configurator)
    {
        $this->configurator = $configurator;
        $_ = $getOpt->getCmdOpt(0, 1);
        if (empty($_)) {
            $this->printHelp();
        } else {
            $operateOpt = array_pop($_);
            $operateVal = $getOpt->getOption($operateOpt);
            $arguments  = $getOpt->getCmdOpt(1);

            if (false === strpos($operateOpt, ':')) {
                throw new \Exception("First argument '$operateOpt' is invalid, It must like '--create:module'");
            }

            $ref = new \ReflectionClass($this);
            list($class, $method) = explode(':', $operateOpt);
            $className = $ref->getNamespaceName() . '\\' . ucfirst(strtolower(trim($class)));
            $method    = strtolower(trim($method));

            if (!class_exists($className)) {
                throw new \Exception("First argument '$operateOpt' is invalid, can't find the Class '$className'");
            }
            $className = '\\' . $className;
            $class = new $className();

            if (!method_exists($class, $method)) {
                throw new \Exception("First argument '$operateOpt' is invalid, can't find the method '$method' of class '$className'");
            }

            return call_user_func([$class, $method], $this, $operateVal, $arguments);
        }
    }

    public function printHelp()
    {
        echo 'this is help';
    }

    public function getCommandList()
    {
        $ref = new \ReflectionObject($this);

        $props = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $ret[$prop->getName()] = $prop->getValue($this);
        }

        return (array) $ret;
    }

    public function getDescription($property)
    {
        if (isset($this->descriptions[$property])) {
            return $this->descriptions[$property];
        }
        return '';
    }

    public function setDescription($property, $description)
    {
        $this->descriptions[$property] = $description;
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            return null;
        }
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

}

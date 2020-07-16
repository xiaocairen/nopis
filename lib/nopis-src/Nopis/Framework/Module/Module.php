<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Module;

use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\DI\ContainerInterface;
use Nopis\Lib\Event\EventManagerInterface;

/**
 * @author Wangbin
 */
abstract class Module
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var \Nopis\Lib\Http\RequestInterface
     */
    protected $request;

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    protected $configurator;

    /**
     * @var \Nopis\Lib\DI\ContainerInterface
     */
    protected $container;

    /**
     * @var \Nopis\Lib\Event\EventManagerInterface
     */
    protected $eventManager;

    /**
     * The construct function can't be reload
     *
     * @param ContainerInterface $container
     * @param EventManagerInterface $eventManager
     */
    final public function __construct(RequestInterface $request, ConfiguratorInterface $configurator, ContainerInterface $container, EventManagerInterface $eventManager)
    {
        $this->request = $request;
        $this->configurator = $configurator;
        $this->container = $container;
        $this->eventManager = $eventManager;
    }

    abstract public function boot();

    /**
     * Returns the Module name (the class short name).
     *
     * @return string The Module name
     *
     * @api
     */
    final public function getName()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        $name = get_class($this);
        $pos = strrpos($name, '\\');

        return $this->name = false === $pos ? $name : substr($name, $pos + 1);
    }

    /**
     * Gets the Module namespace.
     *
     * @return string The Module namespace
     *
     * @api
     */
    final public function getNamespace()
    {
        $class = get_class($this);

        return substr($class, 0, strrpos($class, '\\'));
    }

    /**
     * Gets the Module directory path.
     *
     * @return string The Module absolute path
     *
     * @api
     */
    final public function getPath()
    {
        if (null === $this->path) {
            $reflected = new \ReflectionObject($this);
            $this->path = dirname($reflected->getFileName());
        }

        return $this->path;
    }
}

<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Event;

use Nopis\Lib\DI\ContainerInterface;

/**
 * @author Wangbin
 */
class ListenerInjector implements ListenerInjectorInterface
{

    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @var array
     */
    private $listeners = [];

    /**
     * @var \Nopis\Lib\DI\ContainerInterface
     */
    protected $container = null;

    /**
     * @param \Nopis\Lib\DI\ContainerInterface $container
     */
    private function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Can't be clone
     */
    private function __clone()
    {

    }

    /**
     * @param \Nopis\Lib\DI\ContainerInterface $container
     */
    public static function getInstance(ContainerInterface $container)
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($container);
        }
        return self::$instance;
    }

    /**
     * inject one or more listeners to an Event or an Event's method
     *
     * @param string          $event           The event class name, like '\Namespace\Subnamespace\Class'
     *
     * @param array|callable  $listener        The listener type like:
     * <ul>
     *  <li><b>type 1</b>. ['\NameSpace\ClassName', 'Method']</li>
     *  <li><b>type 2</b>. [new \NameSpace\ClassName(), 'Method']</li>
     *  <li><b>type 3</b>. An anonymous function</li>
     * </ul>
     * highly recommend use type 1
     *
     * @param int             $priority        priority
     *
     * @return \Nopis\Lib\Event\ListenerInjector
     * @throws \InvalidArgumentException
     */
    public function inject($event, $listener, $priority = 0)
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->filterListener($listener, $event);

        $this->listeners[$event][$priority][] = $listener;

        return $this;
    }

    /**
     * Removes an event listener from the specified events.
     *
     * @param string          $event     The event to remove a listener from
     * @param string|callable $listener  The listener to remove
     * @return \Nopis\Lib\Event\ListenerInjector
     */
    public function removeListener($event, $listener)
    {
        if (!isset($this->listeners[$event]) || empty($this->listeners[$event])) {
            return $this;
        }

        foreach ($this->listeners[$event] as $priority => $listeners) {
            if (false !== ($key = array_search($listener, $listeners, true))) {
                unset($this->listeners[$event][$priority][$key]);
            }
        }

        return $this;
    }

    /**
     * Get a listeners list of event by given
     *
     * @param string  $event         the event class name
     * @param boolean $instantiated  if or not instance the listeners
     */
    public function getEventListeners($event = null, $instantiated = false)
    {
        if (null === $event) {
            return $this->listeners;
        }

        if (!isset($this->listeners[$event]) || empty($this->listeners[$event])) {
            return null;
        } elseif (!$instantiated) {
            return $this->listeners[$event];
        }

        foreach ($this->listeners[$event] as $priority => $listeners) {
            foreach ($listeners as $k => $listener) {
                if (is_array($listener) && !is_object($listener[0])) {
                    $this->listeners[$event][$priority][$k] = [$this->getListenerInstance($listener[0]), $listener[1]];
                }
            }
        }

        return $this->listeners[$event];
    }

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $event The event class name
     *
     * @return bool    true if the specified event has any listeners, false otherwise
     */
    public function hasListeners($event = null)
    {
        if (null === $event) {
            return (boolean) count($this->listeners);
        }

        return isset($this->listeners[$event]) ? (boolean) count($this->listeners[$event]) : false;
    }

    /**
     * get a listener's instance
     *
     * @param string $listener
     */
    protected function getListenerInstance($listener)
    {
        return $this->container->get($listener, ['container' => $this->container]);
    }

    /**
     * filter the listener be injected
     *
     * @param array $listener
     * @param string $event
     * @throws \Exception
     */
    protected function filterListener(& $listener, $event)
    {
        if (is_array($listener) && is_string($listener[0])) {
            $listener[0] = '\\' . ltrim(trim($listener[0]), '\\');
            if (!in_array('Nopis\Lib\Event\EventListener', class_parents($listener[0]))) {
                throw new \Exception(
                    sprintf(
                        'Listener class\'%s\' has not extended abstract base class \'%s\'',
                        $listener[0],
                        'Nopis\Lib\Event\EventListener'
                    )
                );
            }
        } elseif (is_array($listener) && is_object($listener[0]) && !$listener[0] instanceof EventListener) {
            throw new \Exception(
                sprintf(
                    'Listener class\'%s\' has not extended from abstract class \'%s\'',
                    get_class($listener[0]),
                    'Nopis\Lib\Event\EventListener'
                )
            );
        } elseif (!is_callable($listener)) {
            $listener = (array) $listener;
            throw new \Exception(
                sprintf(
                    'Inject listener \'%s::%s\' is not callable, at event:%s',
                    is_object($listener[0]) ? get_class($listener[0]) : $listener[0],
                    isset($listener[1]) ? $listener[1] : 'NULL',
                    $event
                )
            );
        }
    }

}

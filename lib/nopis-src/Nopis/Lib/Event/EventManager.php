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
 * @author wangbin
 */
class EventManager implements EventManagerInterface
{

    /**
     * @var \Nopis\Lib\Event\EventManagerInterface
     */
    private static $instance;

    /**
     * @var \Nopis\Lib\Event\ListenerInjectorInterface
     */
    private $listenerInjector;

    /**
     * @var \Nopis\Lib\Event\EventRegistry
     */
    //private $eventRegistry;

    /**
     * Contructor.
     *
     * @param \Nopis\Lib\DI\ContainerInterface $container
     * @param array $eventRegistry  Event registry
     */
    private function __construct(ContainerInterface $container)
    {
        $this->listenerInjector = ListenerInjector::getInstance($container);
    }

    /**
     * broadcast an event to all registered listeners, and execute event method handle if method handle exists.
     *
     * @param Event $event       The event to pass to the event handlers/listeners.
     * @param array $arguments   The arguments of event's method.
     *
     * @return mixed
     * @api
     */
    public function broadcast(Event $event, array $arguments = [])
    {
        if (!($event instanceof Event)) {
            return;
        }

        $event->setManager($this);
        $beforeListeners = $this->getListeners($event->getBeforeEventName(), true);
        $this->doBroadcast($beforeListeners, $event, [], true);

        $listeners = $this->getListeners($event->getEventName(), true);
        return $this->doBroadcast($listeners, $event, $arguments);
    }

    /**
     * Triggers the listeners of an event.
     *
     * This method can be overridden to add functionality that is executed
     * for each listener.
     *
     * @param callable[] $listeners      The event listeners.
     * @param Event      $event          The event object to pass to the event handlers/listeners.
     * @param array      $arguments      The arguments of event's method.
     * @param boolean    $isBefore
     */
    protected function doBroadcast($listeners, Event $event, array $arguments = [], $isBefore = false)
    {
        $ret = null;
        if (!$isBefore && method_exists($event, 'handle')) {
            $ret = call_user_func_array([$event, 'handle'], $arguments);
        }

        if ($listeners) {
            krsort($listeners);
            do {
                $_listeners = current($listeners);
                foreach ($_listeners as $listener) {
                    is_callable($listener) && call_user_func($listener, $event);
                    if ($event->isPropagationStopped())
                        break;
                }
                if ($event->isPropagationStopped())
                    break;
            } while (false !== next($listeners));
        }

        return $ret;
    }

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string          $eventName    The event identify name defined in Event Registry, or event class name.
     * @param array|callable  $listener     The listener type like:
     * <ul>
     *  <li><b>type 1</b>. ['\NameSpace\ClassName', 'Method']</li>
     *  <li><b>type 2</b>. [new \NameSpace\ClassName(), 'Method']</li>
     *  <li><b>type 3</b>. An anonymous function</li>
     * </ul>
     * highly recommend use type 1
     *
     * @param int             $priority               priority
     * @return \Nopis\Lib\Event\EventManager
     */
    public function addListener($eventName, $listener, $priority = 1)
    {
        if (null == $eventName) {
            throw new \Exception('Event name must be string, null be given');
        }

        $this->listenerInjector->inject($eventName, $listener, $priority);
        return $this;
    }

    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param string $eventName The name of the event
     * @param boolean $instantiated  if or not instance the listeners
     *
     * @return array The event listeners for the specified event, or all event listeners by event name
     */
    public function getListeners($eventName = null, $instantiated = false)
    {
        return $this->listenerInjector->getEventListeners($eventName, $instantiated);
    }

    /**
     * Removes an event listener from the specified events.
     *
     * @param string   $eventName      The event to remove a listener from
     * @param array    $listener       The listener to remove, Must notice the callabled listener can't be remove
     * @return \Nopis\Lib\Event\EventManager
     */
    public function removeListener($eventName, array $listener)
    {
        $this->listenerInjector->removeListener($eventName, $listener);

        return $this;
    }

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $eventName The name of the event
     *
     * @return bool    true if the specified event has any listeners, false otherwise
     */
    public function hasListeners($eventName = null)
    {
        return $this->listenerInjector->hasListeners($eventName);
    }

    private function __clone(){}

    /**
     * Return singleton object
     *
     * @param \Nopis\Lib\DI\ContainerInterface $container
     *
     * @return \Nopis\Lib\Event\EventManagerInterface
     */
    public static function getInstance(ContainerInterface $container = null)
    {
        if (!self::$instance instanceof self) {
            if (!$container instanceof ContainerInterface)
                throw new \Exception;
            self::$instance = new self($container);
        }

        return self::$instance;
    }
}

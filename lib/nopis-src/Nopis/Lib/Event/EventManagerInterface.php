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

interface EventManagerInterface
{

    /**
     * Return singleton object
     *
     * @param \Nopis\Lib\DI\ContainerInterface $container
     *
     * @return \Nopis\Lib\Event\EventManagerInterface
     */
    public static function getInstance(ContainerInterface $container = null);

    /**
     * broadcast an event to all registered listeners, and execute event method handle if method handle exists.
     *
     * @param Event $event       The event to pass to the event handlers/listeners.
     * @param array $arguments   The arguments of event's method.
     *
     * @return mixed
     * @api
     */
    public function broadcast(Event $event, array $arguments = []);

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
     * @param int             $priority        priority
     * @return \Nopis\Lib\Event\EventManager
     *
     * @api
     */
    public function addListener($eventName, $listener, $priority = 1);

    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param string $eventName The name of the event
     * @param boolean $instantiated  if or not instance the listeners
     *
     * @return array The event listeners for the specified event, or all event listeners by event name
     */
    public function getListeners($eventName = null, $instantiated = false);

    /**
     * Removes an event listener from the specified events.
     *
     * @param string   $eventName      The event to remove a listener from
     * @param array    $listener       The listener to remove, Must notice the callabled listener can't be remove
     * @return \Nopis\Lib\Event\EventManager
     */
    public function removeListener($eventName, array $listener);

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $eventName The name of the event
     *
     * @return bool    true if the specified event has any listeners, false otherwise
     */
    public function hasListeners($eventName = null);
}
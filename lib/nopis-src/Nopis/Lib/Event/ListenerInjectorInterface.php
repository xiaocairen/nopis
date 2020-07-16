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

/**
 * @author wangbin
 */
interface ListenerInjectorInterface
{

    /**
     * inject one or more listeners to an Event or an Event's method
     *
     * @param string          $event           The event name, like '\Namespace\Subnamespace\Class'
     *
     * @param array|callable  $listener        The listener type like:
     * <ul>
     *  <li>type 1.['\NameSpace\ClassName', 'Method']</li>
     *  <li>type 2.[new \NameSpace\ClassName(), 'Method']</li>
     *  <li>type 3.An anonymous function</li>
     * </ul>
     * highly recommend use type 1
     *
     * @param int             $priority        priority
     *
     * @return \Nopis\Lib\Event\ListenerInjector
     *
     * @api
     */
    public function inject($event, $listener, $priority = 0);

    /**
     * Removes an event listener from the specified events.
     *
     * @param string          $event     The event to remove a listener from
     * @param string|callable $listener  The listener to remove
     * @return \Nopis\Lib\Event\ListenerInjector
     *
     * @api
     */
    public function removeListener($event, $listener);

    /**
     * Get a listeners list of event by given
     *
     * @param string  $event         the event name
     * @param boolean $instantiated  if or not instance the listeners
     *
     * @api
     */
    public function getEventListeners($event = null, $instantiated = false);

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $event The name of the event
     *
     * @return bool    true if the specified event has any listeners, false otherwise
     */
    public function hasListeners($event = null);
}

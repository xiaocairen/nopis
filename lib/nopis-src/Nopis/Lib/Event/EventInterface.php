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

interface EventInterface
{
    /**
     * When disptach event call.
     *
     * @param array $args
     *
     * @api
     */
    // public function handle(array $args = []);

    /**
     * Returns the event source.
     *
     * @see Event::getEventSource
     * @return LatteObject    the event source.
     *
     * @api
     */
    public function getEventSource();

    /**
     * Returns whether further event listeners should be triggered.
     *
     * @see Event::stopPropagation
     * @return bool    Whether propagation was already stopped for this event.
     *
     * @api
     */
    public function isPropagationStopped();

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * stopPropagation().
     *
     * @api
     */
    public function stopPropagation();

    /**
     * Stores the EventManager that dispatches this Event
     *
     * @param \Nopis\Lib\Event\EventManager $eventManager
     * @api
     */
    public function setManager(EventManagerInterface $eventManager);

    /**
     * Returns the EventDispatcher that dispatches this Event
     *
     * @return \Nopis\Lib\Event\EventManager
     * @api
     */
    public function getManager();

    /**
     * Generates the event's unique name.
     *
     * @return string
     * @api
     */
    public static function getEventName();

    /**
     * Generates the event's before unique name.
     *
     * @return string
     * @api
     */
    public static function getBeforeEventName();
}
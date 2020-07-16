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
 * @author Wangbin
 */
abstract class Event implements EventInterface
{

    /**
     * @var Object the Event happened source
     */
    protected $eventSource = null;

    /**
     * @var bool    Whether no further event listeners should be triggered
     */
    private $propagationStopped = false;

    /**
     * @param Object $eventSource  the Event happened source
     */
    public function __construct($eventSource)
    {
        $this->eventSource = $eventSource;
    }

    /**
     * Returns the event source.
     *
     * @return Object
     */
    public function getEventSource()
    {
        return $this->eventSource;
    }

    /**
     * Returns whether further event listeners should be triggered.
     *
     * @see Event::stopPropagation
     * @return bool    Whether propagation was already stopped for this event.
     *
     * @api
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * stopPropagation().
     *
     * @api
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * Stores the EventManager that dispatches this Event
     *
     * @param \Nopis\Lib\Event\EventManager $eventManager
     * @api
     */
    public function setManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = & $eventManager;
    }

    /**
     * Returns the EventManager that dispatches this Event
     *
     * @return \Nopis\Lib\Event\EventManager
     * @api
     */
    public function getManager()
    {
        return $this->eventManager;
    }

    /**
     * Generates the event's unique name.
     *
     * @return string
     * @api
     */
    final public static function getEventName()
    {
        if (0 === strpos(static::class, 'Nopis\\'))
            return '\\' . ltrim(static::class, '\\');

        list($module, $class) = explode('\\Event\\', static::class);
        $module = str_replace('\\', '', $module);

        return $module . '.' . $class;
    }

    /**
     * Generates the event's before unique name.
     *
     * @return string
     * @api
     */
    final public static function getBeforeEventName()
    {
        return 'before.' . static::getEventName();
    }
}
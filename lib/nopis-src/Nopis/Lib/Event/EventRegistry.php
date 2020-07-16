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
 * Description of EventRegistry
 *
 * @author Wangbin
 */
class EventRegistry
{
    /**
     * @var array
     */
    private $eventRegistry;

    public function __construct(array $eventRegistry)
    {
        $this->eventRegistry = $eventRegistry;
    }

    /**
     * Get event name by event identifier which in event registry
     *
     * @param string  $eventIdentifier
     * @return string
     * @throws EventNotFoundException
     */
    public function getEventName($eventIdentifier)
    {
        return isset($this->eventRegistry['events'][$eventIdentifier]) ? $this->eventRegistry['events'][$eventIdentifier] : $eventIdentifier;
    }

    /**
     * Return the defined events in event registry
     *
     * @return array
     */
    public function getDefinedEventsFromEventRegistry()
    {
        return $this->eventRegistry['events'] ?: [];
    }

    /**
     * Return the injected listeners in event registry
     *
     * @return array
     */
    public function getInjectedListenersFromEventRegistry()
    {
        return $this->eventRegistry['listeners'] ?: [];
    }
}

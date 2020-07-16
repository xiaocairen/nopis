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
 * The abstract base class of event listener
 *
 * @author Wangbin
 */
abstract class EventListener implements EventListenerInterface
{
    /**
     * @var \Nopis\Lib\DI\ContainerInterface
     */
    protected $container = null;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    final public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Return container
     *
     * @return \Nopis\Lib\DI\ContainerInterface
     */
    final public function getContainer()
    {
        return $this->container;
    }
}

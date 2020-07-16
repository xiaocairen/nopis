<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\MVC;

use Nopis\Lib\Event\Event as FrameworkEvent;

/**
 * Event which be use only in controller.
 *
 * @author wb
 */
abstract class Event extends FrameworkEvent
{
    /**
     * Constructor.
     *
     * @param \nPub\Core\MVC\Controller $controller
     */
    final public function __construct(Controller $controller)
    {
        parent::__construct($controller);
    }

    /**
     * Returns current controller which event source.
     *
     * @return \nPub\Core\MVC\Controller
     */
    final public function getCurController()
    {
        return parent::getEventSource();
    }
}

<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Module\NopCoreModule\EventListener;

use Nopis\Lib\Event\EventListener;

class LoginListener extends EventListener
{
    public function beforeLogin(\Nopis\Lib\Security\LoginEvent $test)
    {
        echo get_class($test->getEventSource()) . '<br>';
        echo 'i am in before LoginEvent Listener<br>';
    }

    public function afterLogin(\Nopis\Lib\Security\LoginEvent $test)
    {
        echo get_class($test->getEventSource()) . '<br>';
        echo 'i am in after LoginEvent Listener<br>';
    }
}
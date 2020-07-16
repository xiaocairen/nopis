<?php

namespace nPub\Module\NopCoreModule;

use Nopis\Framework\Module\Module;

/**
 * Description of nPubModuleNopCoreModule
 *
 * @author wangbin
 */
class nPubModuleNopCoreModule extends Module
{
    public function boot()
    {
        //$this->eventManager->addListener(\Nopis\Lib\Security\LoginEvent::getBeforeEventName(), ['\nPub\Module\NopCoreModule\EventListener\LoginListener', 'beforeLogin']);
        //$this->eventManager->addListener(\Nopis\Lib\Security\LoginEvent::getEventName(), ['\nPub\Module\NopCoreModule\EventListener\LoginListener', 'afterLogin']);
    }
}

<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Event\Template;

/**
 * Description of Tager
 *
 * @author wb
 */
class Tager
{
    public static function addFrameworkTags(TemplateEngineInvokeEvent $event)
    {
        $event->getEngine()
                ->setUserFuncTag('url',         ['router', $event->getRouter(), 'generateUrl'])
                ->setUserFuncTag('config',      ['config', $event->getConfigurator(), 'getConfig'])
                ->setUserFuncTag('time',        ['xUtil', new \xUtil\TemplateFunc(), 'getTime']);
    }
}

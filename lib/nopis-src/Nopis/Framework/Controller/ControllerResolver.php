<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Controller;

use Nopis\Lib\DI\ContainerInterface;
use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\Routing\RouterInterface;
use Nopis\Lib\Event\EventManagerInterface;
use Nopis\Lib\Security\User\UserCredentials;

/**
 * Description of ControllerResolver
 *
 * @author wangbin_hn
 */
class ControllerResolver
{
    /**
     * @var array
     */
    public $controller = [];

    public function __construct
    (
        array $controller,
        ContainerInterface $container,
        RequestInterface $request,
        ConfiguratorInterface $configurator,
        UserCredentials $userCredentials,
        RouterInterface $router,
        EventManagerInterface $eventManager
    )
    {
        if (!in_array('Nopis\Framework\Controller\Controller', class_parents($controller[0]))) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Controller "%s" must be extended from "Nopis\Framework\Controller\Controller".',
                    $controller[0]
                )
            );
        }

        $ctl = $controller[0];
        $this->controller[0] = new $ctl(
            $container,
            $request,
            $configurator,
            $userCredentials,
            $router,
            $eventManager
        );
        $this->controller[1] = $controller[1];
    }

    /**
     * Return the instanced controller object.
     *
     * @return array
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return \Nopis\Lib\Http\Response
     */
    final public function getHookResponse()
    {
        return $this->controller[0]->getHookResponse();
    }

    /**
     * @return boolean
     */
    final public function hasHookResponse()
    {
        return $this->controller[0]->hasHookResponse();
    }
}

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

use Nopis\Lib\Event\Event;
use Nopis\Lib\Routing\RouterInterface;
use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\DI\ContainerInterface;

/**
 * Description of TplEngineInvokingEvent
 *
 * @author wangbin
 */
class TemplateEngineInvokeEvent extends Event
{

    /**
     * @var \Nopis\Framework\Event\Template\EngineInterface
     */
    private $engine;

    /**
     * @var \Nopis\Lib\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    protected $configurator;

    /**
     * @var \Nopis\Lib\DI\ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param Object $eventSource  the Event happened source
     * @param EngineInterface $engine  Template engine
     */
    public function __construct($eventSource, EngineInterface $engine, RouterInterface $router, ConfiguratorInterface $configurator, ContainerInterface $container)
    {
        $this->engine = $engine;
        $this->router = $router;
        $this->configurator = $configurator;
        $this->container = $container;

        parent::__construct($eventSource);
    }

    /**
     * Call by Event manager
     *
     * @param array $args
     * @return null
     */
    public function handle()
    {
        Tager::addFrameworkTags($this);
        $customTager = $this->configurator->getConfig('framework.template_custom_tager');
        if (!empty($customTager) && (null !== ($customTager = $this->getUserTager($customTager)))) {
            $customTager->addTags($this);
        }
        return null;
    }

    /**
     * @return \Nopis\Framework\Event\Template\EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Return router
     *
     * @return \Nopis\Lib\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Return global configurator
     *
     * @return \Nopis\Lib\Config\ConfiguratorInterface
     */
    public function getConfigurator()
    {
        return $this->configurator;
    }

    /**
     * @param string $userTager
     * @return CustomTagerInterface|null
     */
    protected function getUserTager($userTager)
    {
        $userTager = $this->container->get($userTager);

        return $userTager instanceof UserTagerInterface ? $userTager : null;
    }

}

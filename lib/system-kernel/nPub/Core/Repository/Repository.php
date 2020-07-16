<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\Repository;

use nPub\API\Repository\RepositoryInterface;
use nPub\SPI\Persistence\PersistenceHandlerInterface;
use Nopis\Lib\DI\ContainerInterface;
use Nopis\Lib\Config\ConfiguratorInterface;

/**
 * Description of Repository
 *
 * @author wangbin
 */
class Repository implements RepositoryInterface
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @var \nPub\SPI\Persistence\PersistenceHandlerInterface
     */
    private $persistenceHandler;

    /**
     * @var \Nopis\Lib\DI\ContainerInterface
     */
    private $container;

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    private $configurator;

    /**
     * @var \nPub\API\Repository\UserServiceInterface
     */
    private $userService;

    /**
     * @var \nPub\API\Repository\UserGroupServiceInterface
     */
    private $userGroupService;

    /**
     * @var \nPub\API\Repository\AdminGroupServiceInterface
     */
    private $adminGroupService;

    /**
     * @var \nPub\API\Repository\BackendMapServiceInterface
     */
    private $backendMapService;

    /**
     * @var \nPub\API\Repository\ContentServiceInterface
     */
    private $contentService;

    /**
     * @var \nPub\API\Repository\ClassifyServiceInterface
     */
    private $classifyService;

    /**
     * Constructor.
     *
     * @param \nPub\SPI\Persistence\PersistenceHandlerInterface $persistenceHandler
     * @param \Nopis\Lib\DI\ContainerInterface $container
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     */
    private function __construct(PersistenceHandlerInterface $persistenceHandler, ContainerInterface $container, ConfiguratorInterface $configurator)
    {
        $this->persistenceHandler = $persistenceHandler;
        $this->container = $container;
        $this->configurator = $configurator;
    }

    private function __clone(){}

    /**
     * Get class instance.
     *
     * @param \nPub\SPI\Persistence\PersistenceHandlerInterface $persistenceHandler
     * @param \Nopis\Lib\DI\ContainerInterface $container
     *
     * @return \nPub\Core\Repository\Repository
     */
    public static function getInstance(PersistenceHandlerInterface $persistenceHandler, ContainerInterface $container, ConfiguratorInterface $configurator)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self($persistenceHandler, $container, $configurator);
        }

        return self::$instance;
    }

    /**
     * Get user service.
     *
     * @return \nPub\API\Repository\UserServiceInterface
     */
    public function getUserService()
    {
        if ( null === $this->userService ) {
            $this->userService = new UserService($this, $this->persistenceHandler->userHandler());
        }

        return $this->userService;
    }

    /**
     * Get user group service.
     *
     * @return \nPub\API\Repository\UserGroupServiceInterface
     */
    public function getUserGroupService()
    {
        if ( null === $this->userGroupService ) {
            $this->userGroupService = new UserGroupService($this, $this->persistenceHandler->userGroupHandler());
        }

        return $this->userGroupService;
    }

    /**
     * Get admin service.
     *
     * @return \nPub\API\Repository\AdminGroupServiceInterface
     */
    public function getAdminGroupService()
    {
        if ( null === $this->adminGroupService ) {
            $this->adminGroupService = new AdminGroupService($this, $this->persistenceHandler->adminGroupHandler());
        }

        return $this->adminGroupService;
    }

    /**
     * Get backend Map service.
     *
     * @return \nPub\API\Repository\BackendMapServiceInterface
     */
    public function getBackendMapService()
    {
        if ( $this->backendMapService == null )
            $this->backendMapService = new BackendMapService($this, $this->persistenceHandler->backendMapHandler());

        return $this->backendMapService;
    }

    /**
     * Get user service.
     *
     * @return \nPub\API\Repository\ContentServiceInterface
     */
    public function getContentService()
    {
        if ( $this->contentService == null )
            $this->contentService = new ContentService($this, $this->persistenceHandler->contentHandler());

        return $this->contentService;
    }

    /**
     * Get classify service.
     *
     * @return \nPub\API\Repository\ClassifyServiceInterface
     */
    public function getClassifyService(string $table = 'classify')
    {
        if ( $this->classifyService == null )
            $this->classifyService = new ClassifyService($this, $this->persistenceHandler->classifyHandler());
        $this->classifyService->setTable($table);

        return $this->classifyService;
    }

    /**
     * Get DI container.
     *
     * @return \Nopis\Lib\DI\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get system configurator.
     *
     * @return \Nopis\Lib\Config\ConfiguratorInterface
     */
    public function getConfigurator()
    {
        return $this->configurator;
    }
}

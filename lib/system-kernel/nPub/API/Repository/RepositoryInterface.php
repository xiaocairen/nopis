<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\API\Repository;

/**
 *
 * @author wangbin
 */
interface RepositoryInterface
{
    /**
     * Get user service.
     *
     * @return \nPub\API\Repository\UserServiceInterface
     */
    public function getUserService();

    /**
     * Get group service.
     *
     * @return \nPub\API\Repository\UserGroupServiceInterface
     */
    public function getUserGroupService();

    /**
     * Get admin service.
     *
     * @return \nPub\API\Repository\AdminGroupServiceInterface
     */
    public function getAdminGroupService();

    /**
     * Get backend Map service.
     *
     * @return \nPub\API\Repository\BackendMapServiceInterface
     */
    public function getBackendMapService();

    /**
     * Get user service.
     *
     * @return \nPub\API\Repository\ContentServiceInterface
     */
    public function getContentService();

    /**
     * Get folder service.
     *
     * @return \nPub\API\Repository\ClassifyServiceInterface
     */
    public function getClassifyService(string $table = 'classify');

    /**
     * Get DI container.
     *
     * @return \Nopis\Lib\DI\ContainerInterface
     */
    public function getContainer();

    /**
     * Get system configurator.
     *
     * @return \Nopis\Lib\Config\ConfiguratorInterface
     */
    public function getConfigurator();
}

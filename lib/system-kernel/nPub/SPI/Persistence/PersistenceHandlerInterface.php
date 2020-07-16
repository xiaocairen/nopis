<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence;

/**
 * @author wangbin
 */
interface PersistenceHandlerInterface
{
    /**
     * @return \nPub\SPI\Persistence\User\UserHandlerInterface
     */
    public function userHandler();

    /**
     * @return \nPub\SPI\Persistence\User\GroupHandlerInterface
     */
    public function userGroupHandler();

    /**
     * @return \nPub\SPI\Persistence\Admin\GroupHandlerInterface
     */
    public function adminGroupHandler();

    /**
     * @return \nPub\SPI\Persistence\Backend\BackendMapHandlerInterfce
     */
    public function backendMapHandler();

    /**
     * @return \nPub\SPI\Persistence\Content\ContentHandlerInterface
     */
    public function contentHandler();

    /**
     * @return \nPub\SPI\Persistence\Classify\ClassifyHandlerInterface
     */
    public function classifyHandler();
}

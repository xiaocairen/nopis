<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\Persistence;

use nPub\SPI\Persistence\PersistenceHandlerInterface;
use Nopis\Lib\Database\DBInterface;
use nPub\Core\Persistence\User\UserHandler;
use nPub\Core\Persistence\User\UserGroupHandler;
use nPub\Core\Persistence\Admin\AdminGroupHandler;
use nPub\Core\Persistence\Backend\BackendMapHandler;
use nPub\Core\Persistence\Content\ContentHandler;
use nPub\Core\Persistence\Classify\ClassifyHandler;

/**
 * Description of PersistenceHandler
 *
 * @author wangbin
 */
class PersistenceHandler implements PersistenceHandlerInterface
{
    /**
     * @var \Nopis\Lib\Database\DBInterface
     */
    private $pdo;

    /**
     * @var \nPub\SPI\Persistence\User\UserHandlerInterface
     */
    private $userHandler;

    /**
     * @var \nPub\SPI\Persistence\User\GroupHandlerInterface
     */
    private $userGroupHandler;

    /**
     * @var \nPub\SPI\Persistence\Admin\GroupHandlerInterface
     */
    private $adminGroupHandler;

    /**
     * @var \nPub\SPI\Persistence\Backend\BackendMapHandlerInterfce
     */
    private $backendMapHandler;

    /**
     * @var \nPub\SPI\Persistence\Content\ContentHandlerInterface
     */
    private $contentHandler;

    /**
     * @var \nPub\SPI\Persistence\Classify\ClassifyHandlerInterface
     */
    private $classifyHandler;

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\Database\DBInterface $pdo
     */
    public function __construct(DBInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return \nPub\SPI\Persistence\User\UserHandlerInterface
     */
    public function userHandler()
    {
        if ( null === $this->userHandler ) {
            $this->userHandler = new UserHandler($this->pdo);
        }

        return $this->userHandler;
    }

    /**
     * @return \nPub\SPI\Persistence\User\GroupHandlerInterface
     */
    public function userGroupHandler()
    {
        if ( null === $this->userGroupHandler ) {
            $this->userGroupHandler = new UserGroupHandler($this->pdo);
        }

        return $this->userGroupHandler;
    }

    /**
     * @return \nPub\SPI\Persistence\Admin\GroupHandlerInterface
     */
    public function adminGroupHandler()
    {
        if ( null === $this->adminGroupHandler ) {
            $this->adminGroupHandler = new AdminGroupHandler($this->pdo);
        }

        return $this->adminGroupHandler;
    }

    /**
     * @return \nPub\SPI\Persistence\Backend\BackendMapHandlerInterfce
     */
    public function backendMapHandler()
    {
        if ( null === $this->backendMapHandler ) {
            $this->backendMapHandler = new BackendMapHandler($this->pdo);
        }

        return $this->backendMapHandler;
    }

    /**
     * @return \nPub\SPI\Persistence\Content\ContentHandlerInterface
     */
    public function contentHandler()
    {
        if ( null === $this->contentHandler ) {
            $this->contentHandler = new ContentHandler($this->pdo);
        }

        return $this->contentHandler;
    }

    /**
     * @return \nPub\SPI\Persistence\Classify\ClassifyHandlerInterface
     */
    public function classifyHandler()
    {
        if ( null === $this->classifyHandler ) {
            $this->classifyHandler = new ClassifyHandler($this->pdo);
        }

        return $this->classifyHandler;
    }
}

<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Content;

use Nopis\Lib\Database\TableInterface;
use Nopis\Lib\Pagination\Query\QueryAdapter;
use Nopis\Lib\Config\Configurator;
use Nopis\Lib\DI\Container;
use Nopis\Lib\Entity\ValueObject;

/**
 * Description of SPIBase
 *
 * @author wb
 */
abstract class SPIBase extends ValueObject implements TableInterface
{

    /**
     * @var \nPub\API\Repository\RepositoryInterface
     */
    private static $repository;

    /**
     * @var \Nopis\Lib\Database\DBInterface
     */
    private static $pdo;

    /**
     * @var \Nopis\Lib\Redis\PhpRedis
     */
    private static $redis;

    /**
     * @var \Service\ServiceDelegate
     */
    private static $service;

    /**
     * @return \nPub\Core\MVC\Controller
     */
    private static function getController()
    {
        return Container::getInstance()->getShared('nopis.framework.controller');
    }

    /**
     * @return \Nopis\Lib\Pagination\QueryAdapterInterface
     */
    public static function getQueryAdapter()
    {
        return new QueryAdapter( self::DB() );
    }

    /**
     * @return \Nopis\Lib\Config\ConfiguratorInterface
     */
    protected static function getConfigurator()
    {
        return Configurator::getInstance();
    }

    /**
     * @return \nPub\API\Repository\RepositoryInterface
     */
    protected static function getRepository()
    {
        if ( null === self::$repository )
            self::$repository = self::getController()->getRepository();

        return self::$repository;
    }

    /**
     * @return \Nopis\Lib\Database\DBInterface
     */
    protected static function DB()
    {
        if ( null == self::$pdo )
            self::$pdo = self::getController()->DB();

        return self::$pdo;
    }

    /**
     * @param bool $writeable
     * @return \Nopis\Lib\Redis\PhpRedis
     */
    protected static function getRedis(bool $writeable = false)
    {
        if (null == self::$redis)
            self::$redis = self::getController()->getRedis($writeable);

        return self::$redis;
    }

    /**
     * @return \nPub\API\Repository\ContentServiceInterface
     */
    protected static function getContentService()
    {
        return self::getRepository()->getContentService();
    }

    /**
     * @return \Service\ServiceDelegateInterface
     */
    protected static function getService()
    {
        if (null == self::$service) {
            self::$service = Container::getInstance()->getShared('nopis.framework.service');
        }
        return self::$service;
    }
}

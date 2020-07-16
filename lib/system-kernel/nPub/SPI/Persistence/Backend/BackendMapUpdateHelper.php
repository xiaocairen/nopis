<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Backend;

use nPub\SPI\Persistence\Entity\UpdateHelper;

/**
 * Description of BackendMapUpdateHelper
 *
 * @author wb
 */
class BackendMapUpdateHelper extends UpdateHelper
{

    /**
     * Constructor.
     *
     * @param \nPub\SPI\Persistence\Backend\BackendMap $backendMap
     */
    public function __construct(BackendMap $backendMap)
    {
        parent::__construct($backendMap);
    }

    /**
     * @return \nPub\SPI\Persistence\Backend\BackendMap
     */
    public function getEntity()
    {
        return $this->entity;
    }
}

<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Admin;

use nPub\SPI\Persistence\Entity\CreateHelper;

/**
 * Description of GroupCreateHelper
 *
 * @author wangbin
 */
class AdminGroupCreateHelper extends CreateHelper
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get entity object
     *
     * @return \nPub\SPI\Persistence\Admin\AdminGroup
     */
    public function getEntity()
    {
        return parent::getEntity();
    }

    /**
     * @param array $arguments
     */
    protected function setEntity(array $arguments)
    {
        $this->entity = new AdminGroup($arguments);
    }

}

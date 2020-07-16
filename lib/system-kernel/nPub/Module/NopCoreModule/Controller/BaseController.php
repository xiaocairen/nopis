<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Module\NopCoreModule\Controller;

use nPub\Core\MVC\Controller;

/**
 * @author wangbin
 */
class BaseController extends Controller
{

    /**
     * @return \nPub\API\Repository\AdminGroupServiceInterface
     */
    protected function getAdminGroupService()
    {
        return $this->getRepository()->getAdminGroupService();
    }

    /**
     * @return \nPub\API\Repository\BackendMapServiceInterface
     */
    protected function getBackendMapService()
    {
        return $this->getRepository()->getBackendMapService();
    }

    /**
     * @return \nPub\API\Repository\ContentServiceInterface
     */
    protected function getContentService()
    {
        return $this->getRepository()->getContentService();
    }
}

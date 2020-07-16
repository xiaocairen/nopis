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

/**
 * Description of SiteController
 *
 * @author wangbin
 */
class SiteController extends CommonController
{
    /**
     * 站点设置
     */
    public function setting()
    {
        return $this->render('nPubModuleNopCoreModule:default:site_setting');
    }
}

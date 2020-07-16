<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IndexModule\Controller;

use nPub\Core\MVC\Controller;

/**
 * Description of DefaultController
 *
 * @author Administrator
 */
class DefaultController extends Controller
{
    public function index()
    {
        return $this->render('IndexModule:default:index', [
            'site' => 'nopis At ' . SYS_DATETIME,
        ]);
    }

    public function test()
    {
        var_dump(strtotime('2020-04-01 00:00:01'));
        var_dump(strtotime('2020-04-01'));
        return $this->response->setContent(SYS_DATETIME);
    }
}
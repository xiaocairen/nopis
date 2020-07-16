<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace {
    define('DS', DIRECTORY_SEPARATOR);
}

namespace bootstrap {

    use Nopis\Lib\Config\Configurator;
    use Nopis\Lib\Http\Request;
    use Nopis\Framework\Application;
    use NopisAutoload;

    require __DIR__ . DS . 'autoload.php';
    (new NopisAutoload(include __DIR__ . DS . 'autoload_namespace.php'))->registerAutoload();

    include __DIR__ . DS . 'helpers.php';

    class Bootstrap
    {
        public static function getApp()
        {
            return new Application(Request::getInstance(), Configurator::getInstance(), include __DIR__ . DS . 'routes.php');
        }
    }
}
<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Console;

use Nopis\Lib\Console\Action\CreateModule;
use Nopis\Lib\Console\Action\CreateEntity;

/**
 * Description of Create
 *
 * @author wangbin
 */
class Create
{

    /**
     * Create an Nopis's module
     * use eg. '/path/to/php ./pub/console --create:module Blog/IndexModule'
     * // =============================================================================
     * // The module's name only allow like 'Admin/IndexModule' or 'DefaultModule'
     * //
     * // eg. 'admin/IndexModule', 'Admin/indexModule' and 'Admin/IndexModules'
     * //     'Admin/Indexmodule', and 'Defaultmodule'
     * //     'defaultModule',  'DefaultModules' and 'DefaultModules' is not allowed
     * // =============================================================================
     *
     * @param \Nopis\Lib\Console\OptCommand $optCmd
     * @param string $moduleName
     * @return string
     * @throws \Exception
     */
    public function module(OptCommand $optCmd, $moduleName)
    {
       $cm = new CreateModule();

       return $cm->create($optCmd, $moduleName);
    }

    /**
     * Create an Nopis's database entity.
     * use eg. '/path/to/php ./pub/console --create:entity /path/to/generator_config.neon'
     *
     * @param \Nopis\Lib\Console\OptCommand $optCmd
     * @param string $configParam
     * @return string
     * @throws \Exception
     */
    public function entity(OptCommand $optCmd, $configParam)
    {
        $ce = new CreateEntity();

        return $ce->create($optCmd, $configParam);
    }

}

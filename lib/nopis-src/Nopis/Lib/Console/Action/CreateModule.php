<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Console\Action;

use Nopis\Lib\FileSystem\Dir;
use Nopis\Lib\Console\OptCommand;

/**
 * Description of Create
 *
 * @author wangbin
 */
class CreateModule
{

    /**
     * @var \Nopis\Lib\Console\OptCommand
     */
    private $optCmd;

    /**
     * the Module's name who will be create
     *
     * @var string
     */
    private $moduleName;
    private $moduleNameSpace;
    private $moduleDir;
    private $controllerDir;
    private $eventDir;
    private $listenerDir;
    private $viewDir;

    /**
     * Init all properties
     */
    public function __construct()
    {
        $this->moduleName      = null;
        $this->moduleNameSpace = null;
        $this->moduleDir       = null;
        $this->controllerDir   = null;
        $this->viewDir         = [];
    }

    /**
     * Create an Nopis's module
     *
     * @param \Nopis\Lib\Console\OptCommand $optCmd
     * @param string $moduleName
     * @return string
     * @throws \Exception
     */
    public function create(OptCommand $optCmd, $moduleName)
    {
        $this->optCmd = $optCmd;

        // =============================================================================
        // The module's name only allow like 'Admin/IndexModule' or 'DefaultModule'
        //
        // eg. 'admin/IndexModule', 'Admin/indexModule' and 'Admin/IndexModules'
        //     'Admin/Indexmodule', and 'Defaultmodule'
        //     'defaultModule',  'DefaultModules' and 'DefaultModules' is not allowed
        // =============================================================================
        if (!preg_match('/^[A-Z][a-zA-Z0-9]+(\/[A-Z])?[a-zA-Z0-9]+Module$/', $moduleName)) {
            throw new \Exception("The Module's name '$moduleName' is invaild");
        }

        $this->moduleDir = $this->optCmd->configurator->getSrcDir() . '/' . $moduleName;
        if (file_exists($this->moduleDir)) {
            throw new \Exception("The Module '$moduleName' already exists");
        }

        $this->moduleName      = $moduleName;
        $this->moduleNameSpace = trim(str_replace('/', '\\', $this->moduleName), '\\');

        if ($this->createModuleDir()) {
            // Create controller's dir and controller
            $this->createModuleControllerDir() && $this->createModuleDefaultController();
            // Create dir of the event
            $this->createModuleEventDir();
            // Create dir of the event's listener
            $this->createModuleEventListenerDir();
            // Create view's dir and default template
            $this->createModuleViewDir() && $this->createModuleDefaultTemplate();
            // Create the Module initialize file, which need be called when the Module load
            $this->createModuleLoaderFile();
        }

        return 'The Module \'' . $moduleName . '\' already be created!';
    }

    private function createModuleDir()
    {
        if (!Dir::create($this->moduleDir)) {
            throw new \Exception('Can\'t create the dir\'' . $this->moduleDir . '\'');
        }
        return true;
    }

    private function createModuleControllerDir()
    {
        $this->controllerDir = $this->moduleDir . '/Controller';
        if (!Dir::create($this->controllerDir)) {
            throw new \Exception('Can\'t create the dir\'' . $this->controllerDir . '\'');
        }
        return true;
    }

    private function createModuleDefaultController()
    {
        $frameworkBaseCtl = $this->optCmd->configurator->getConfig('framework.baseController');
        $frameworkBaseCtl = trim($frameworkBaseCtl, '\\');
        $baseCtlName      = substr($frameworkBaseCtl, strrpos($frameworkBaseCtl, '\\') + 1);
        $controller       = $this->controllerDir . '/DefaultController.php';
        $modPrefix        = str_replace('\\', '', $this->moduleNameSpace);
        $content          = <<<EOS
<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace {$this->moduleNameSpace}\Controller;

use {$frameworkBaseCtl};

/**
 * Description of DefaultController
 *
 * @author Administrator
 */
class DefaultController extends {$baseCtlName}
{

    public function indexAction()
    {
        \$modName = '{$this->moduleName}';
        return \$this->render('{$modPrefix}:default:index', [
            'modName' => \$modName,
        ]);
    }
}
EOS;
        $file = new \SplFileObject($controller, 'w+');
        return (boolean) $file->fwrite($content);
    }

    private function createModuleEventDir()
    {
        $this->eventDir = $this->moduleDir . '/Event';
        if (!Dir::create($this->eventDir)) {
            throw new \Exception('Cannot create the event dir\'' . $this->eventDir . '\'');
        }
        return true;
    }

    private function createModuleEventListenerDir()
    {
        $this->listenerDir = $this->moduleDir . '/EventListener';
        if (!Dir::create($this->listenerDir)) {
            throw new \Exception('Cannot create the event listener dir\'' . $this->listenerDir . '\'');
        }
        return true;
    }

    private function createModuleViewDir()
    {
        // $this->viewDir = $this->moduleDir . '/View/default';
        // 2016-04-11
        // 重新定义模版路径，将原来分散在每个模块目录下面的模版，统一集中到/web/view下面

        // 2016-05-24 上面的注释废弃，重新调整为在每个独立的模块下和/web/view目录下各建一个模版目录，
        // 模版机制改为系统在寻找模版时，，优先到/web/view下面寻找，，如果/web/view下面没有模版，
        // 系统再到对应模块目录下的/View目录中查找对应的模块，，实现可自动替换模版机制
        $this->viewDir[] = $this->moduleDir . '/View/default';
        $this->viewDir[] = $this->optCmd->configurator->getWebDir() . '/_view/' . str_replace('/', '', $this->moduleName) . '/default';
        foreach ($this->viewDir as $dir) {
            if (!Dir::create($dir)) {
                throw new \Exception('Cannot create the view dir\'' . $dir . '\'');
            }
        }

        return true;
    }

    private function createModuleDefaultTemplate()
    {
        foreach ($this->viewDir as $dir) {
            $view = $dir . '/index.html';
            try {
                $file = new \SplFileObject($view, 'w+');
                if (!$file->fwrite('Hi! It\'s {{$modName}}')) {
                    throw new \Exception("Cannot write string into the file '{$view}'");
                }
            } catch (\RuntimeException $e) {
                throw new \Exception("Cannot open the file '{$view}'");
            }
        }

        return true;
    }

    private function createModuleLoaderFile()
    {
        $modPrefix = str_replace('\\', '', $this->moduleNameSpace);
        $modLoader = $this->moduleDir . '/' . $modPrefix . '.php';
        $content   = <<<EOS
<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace {$this->moduleNameSpace};

use Nopis\Framework\Module\Module;

/**
 * Description of {$modPrefix}
 *
 * @author Administrator
 */
class {$modPrefix} extends Module
{
    public function boot()
    {
    }
}
EOS;
        $file = new \SplFileObject($modLoader, 'w+');
        return (boolean) $file->fwrite($content);
    }

}

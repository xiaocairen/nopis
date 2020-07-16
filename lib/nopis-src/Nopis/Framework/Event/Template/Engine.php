<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Event\Template;

use Nopis\Lib\Latte\Engine as LatteEngine;
use Nopis\Lib\Routing\RouterInterface;
use Nopis\Lib\Config\ConfiguratorInterface;

/**
 * Description of Engine
 *
 * @author wangbin
 */
class Engine implements EngineInterface
{
    /**
     * @var \Nopis\Lib\Latte\Engine
     */
    protected $engine;

    /**
     * @var \Nopis\Lib\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    protected $configurator;

    /**
     * Constructor.
     *
     * @param Object $eventSource  the Event happened source
     * @param LatteEngine $engine
     * @param RouterInterface $router
     * @param ConfiguratorInterface $configurator
     */
    public function __construct(LatteEngine $engine, RouterInterface $router, ConfiguratorInterface $configurator)
    {
        $this->engine = $engine;
        $this->router = $router;
        $this->configurator = $configurator;
        // $this->engine->getParser()->setSyntax('python');
    }

    /**
     * Return the rendered template content
     *
     * @param string          $template            Template name like 'IndexModule:default:index.latte'
     *                                             or 'IndexModule::header.latte' , 'IndexModule::footer.latte'
     * @param array           $params
     * @param string|null     $templateCacheDir    Template cache dir
     *
     * @return string   the rendered template content
     */
    public function render($template, array $params = [], $templateCacheDir = null)
    {
        $this->engine->setTempDirectory($templateCacheDir);
        return $this->engine->renderToString($this->resolveTemplate($template), $params);
    }

    /**
     * Set user defined function tag
     *
     * @param string $tag       tag used in template
     * @param array  $funcArr   [name, object, method]
     * @return \Nopis\Framework\Event\Template\Engine
     */
    public function setUserFuncTag($tag, array $funcArr)
    {
        $this->engine->addFunc($tag, $funcArr);

        return $this;
    }

    /**
     * Return all user defined function tags
     *
     * @return array
     */
    public function getAllUserFuncTags()
    {
        return $this->engine->getAllUserFuncTags();
    }

    /**
     * Return the template absolute path
     *
     * @param string $modTemplateName   module template name like 'IndexModule:default:index.latte'
     *                                  or 'IndexModule::header.latte' , 'IndexModule::footer.latte'
     *
     * @return string   template absolute path
     */
    private function resolveTemplate($modTemplateName)
    {
        list($moduleName, $dir, $templateName) = explode(':', $modTemplateName, 3);
        if (!$moduleName || !$templateName) {
            throw new NotFoundTemplate($modTemplateName, sprintf('Unable to parse the template "%s"', $modTemplateName));
        }

        foreach ($this->router->getRouteCollection() as $_route) {
            if ($moduleName === $_route->getModName()) {
                $route = & $_route;
                break;
            }
        }

        if (!isset($route)) {
            throw new NotFoundTemplate($modTemplateName, sprintf('The module "%s" in template path "%s" not exists', $moduleName, $modTemplateName));
        }

        // 2016-05-24 模版机制重新调整为系统在寻找模版时，，优先到/web/_view下面寻找，，如果/web/_view下面没有模版，
        // 系统再到对应模块目录下的/View目录中查找对应的模块，，实现可自动替换模版机制
        $templatePath = $this->configurator->getWebDir() . '/_view/' . $moduleName . '/' . $dir . '/' . $templateName . '.html';
        if (!file_exists($templatePath)) {
            $templateInMod = $route->getModPath() . '/View/' . ($dir ? $dir . '/' : '') . $templateName . '.html';
            if (!file_exists($templateInMod)) {
                throw new NotFoundTemplate($modTemplateName, sprintf('The template file "%s" not exists', $templatePath));
            }
            $templatePath = $templateInMod;
            unset($templateInMod);
        }

        return $templatePath;
    }
}

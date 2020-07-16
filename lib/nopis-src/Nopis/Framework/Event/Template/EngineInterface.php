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

/**
 *
 * @author wangbin
 */
interface EngineInterface
{
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
    public function render($template, array $params = [], $templateCacheDir = null);

    /**
     * Set user defined function tag
     *
     * @param string $tag       tag used in template
     * @param array  $funcArr   [name, object, method]
     * @return \Nopis\Framework\Event\Template\EngineInterface
     */
    public function setUserFuncTag($tag, array $funcArr);

    /**
     * Return all user defined function tags
     *
     * @return array
     */
    public function getAllUserFuncTags();
}

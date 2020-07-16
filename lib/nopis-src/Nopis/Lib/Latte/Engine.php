<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 */

namespace Nopis\Lib\Latte;

/**
 * Templating engine Latte.
 *
 * @author     David Grudl
 */
class Engine extends LatteObject
{

    /** Content types */
    const CONTENT_HTML = Compiler::CONTENT_HTML,
            CONTENT_XHTML = Compiler::CONTENT_XHTML,
            CONTENT_XML = Compiler::CONTENT_XML,
            CONTENT_JS = Compiler::CONTENT_JS,
            CONTENT_CSS = Compiler::CONTENT_CSS,
            CONTENT_ICAL = Compiler::CONTENT_ICAL,
            CONTENT_TEXT = Compiler::CONTENT_TEXT;

    /** @var array */
    public $onCompile = array();

    /** @var Parser */
    private $parser;

    /** @var Compiler */
    private $compiler;

    /** @var ILoader */
    private $loader;

    /** @var string */
    private $contentType = self::CONTENT_HTML;

    /** @var string */
    private $tempDirectory;

    /** @var bool */
    private $autoRefresh = TRUE;

    /** @var array run-time filters */
    private $filters = array(
        NULL                => array(), // dynamic
        'bytes'             => 'Nopis\Lib\Latte\Runtime\Filters::bytes',
        'capitalize'        => 'Nopis\Lib\Latte\Runtime\Filters::capitalize',
        'datastream'        => 'Nopis\Lib\Latte\Runtime\Filters::dataStream',
        'date'              => 'Nopis\Lib\Latte\Runtime\Filters::date',
        'escapecss'         => 'Nopis\Lib\Latte\Runtime\Filters::escapeCss',
        'escapehtml'        => 'Nopis\Lib\Latte\Runtime\Filters::escapeHtml',
        'escapehtmlcomment' => 'Nopis\Lib\Latte\Runtime\Filters::escapeHtmlComment',
        'escapeical'        => 'Nopis\Lib\Latte\Runtime\Filters::escapeICal',
        'escapejs'          => 'Nopis\Lib\Latte\Runtime\Filters::escapeJs',
        'escapeurl'         => 'rawurlencode',
        'escapexml'         => 'Nopis\Lib\Latte\Runtime\Filters::escapeXML',
        'firstupper'        => 'Nopis\Lib\Latte\Runtime\Filters::firstUpper',
        'implode'           => 'implode',
        'indent'            => 'Nopis\Lib\Latte\Runtime\Filters::indent',
        'lower'             => 'Nopis\Lib\Latte\Runtime\Filters::lower',
        'nl2br'             => 'Nopis\Lib\Latte\Runtime\Filters::nl2br',
        'number'            => 'number_format',
        'repeat'            => 'str_repeat',
        'replace'           => 'Nopis\Lib\Latte\Runtime\Filters::replace',
        'replacere'         => 'Nopis\Lib\Latte\Runtime\Filters::replaceRe',
        'safeurl'           => 'Nopis\Lib\Latte\Runtime\Filters::safeUrl',
        'strip'             => 'Nopis\Lib\Latte\Runtime\Filters::strip',
        'striptags'         => 'strip_tags',
        'substr'            => 'Nopis\Lib\Latte\Runtime\Filters::substring',
        'trim'              => 'Nopis\Lib\Latte\Runtime\Filters::trim',
        'truncate'          => 'Nopis\Lib\Latte\Runtime\Filters::truncate',
        'upper'             => 'Nopis\Lib\Latte\Runtime\Filters::upper',
    );

    /** @var string */
    private $baseTemplateClass = 'Nopis\Lib\Latte\Template';

    /**
     * ***************************************************
     * 2015-01-17 新添加类属性
     * 为实现使模版可以解析如{$user.name.firsts}这样的变量写法
     * ***************************************************
     * @var array
     */
    private $templateParams = [];

    /** @var array */
    private $userFuncs = [];

    /**
     * Renders template to output.
     * @return void
     */
    public function render($name, array $params = [])
    {
        /*
         * ***************************************************
         * 下面一行为 2015-01-17 新添加
         * 实现使模版可以解析如{$user.name.firsts}这样的变量写法
         * ***************************************************
         */
        $this->templateParams = $params;
        $template = new $this->baseTemplateClass(array_merge($this->userFuncs, $params), $this->filters, $this, $name);
        $this->loadCacheFile($name, $template->getParameters());
    }

    /**
     * Renders template to string.
     * @return string
     */
    public function renderToString($name, array $params = array())
    {
        ob_start();
        try {
            $this->render($name, $params);
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
        return ob_get_clean();
    }

    /**
     * Compiles template to PHP code.
     * @return string
     */
    public function compile($name)
    {
        foreach ($this->onCompile ? : array() as $cb) {
            call_user_func(Helpers::checkCallback($cb), $this);
        }
        $this->onCompile = array();

        $source = $this->getLoader()->getContent($name);
        try {
            $tokens = $this->getParser()->setContentType($this->contentType)
                    ->parse($source);
            $code   = $this->getCompiler()->setContentType($this->contentType)
                    ->compile($tokens);

            if (!preg_match('#\n|\?#', $name)) {
                $code = "<?php\n// source: $name\n?>" . $code;
            }
        } catch (\Exception $e) {
            $e = $e instanceof CompileException ? $e : new CompileException("Thrown exception '{$e->getMessage()}'", NULL, $e);
            throw $e->setSource($source, $this->getCompiler()->getLine(), $name);
        }
        $code = Helpers::optimizePhp($code);
        return $code;
    }

    /**
     * @return void
     */
    private function loadCacheFile($name, $params)
    {
        if (!$this->tempDirectory) {
            return call_user_func(function() {
                foreach (func_get_arg(1) as $__k => $__v) {
                    $$__k = $__v;
                }
                unset($__k, $__v);
                eval('?>' . func_get_arg(0));
            }, $this->compile($name), $params);
        }

        $file   = $this->getCacheFile($name);
        $handle = fopen($file, 'c+');
        if (!$handle) {
            throw new \RuntimeException("Unable to open or create file '$file'.");
        }
        flock($handle, LOCK_SH);
        $stat = fstat($handle);
        if (!$stat['size'] || ($this->autoRefresh && $this->getLoader()->isExpired($name, $stat['mtime']))) {
            ftruncate($handle, 0);
            flock($handle, LOCK_EX);
            $stat = fstat($handle);
            if (!$stat['size']) {
                $code = $this->compile($name);
                if (fwrite($handle, $code, strlen($code)) !== strlen($code)) {
                    ftruncate($handle, 0);
                    throw new \RuntimeException("Unable to write file '$file'.");
                }
            }
            flock($handle, LOCK_SH); // holds the lock
        }

        call_user_func(function() {
            foreach (func_get_arg(1) as $__k => $__v) {
                $$__k = $__v;
            }
            unset($__k, $__v);
            include func_get_arg(0);
        }, $file, $params);
    }

    /**
     * @return string
     */
    public function getCacheFile($name)
    {
        if (!$this->tempDirectory) {
            throw new \RuntimeException('Set path to temporary directory using setTempDirectory().');
        } elseif (!is_dir($this->tempDirectory)) {
            @mkdir($this->tempDirectory); // High concurrency
            if (!is_dir($this->tempDirectory)) {
                throw new \RuntimeException("Temporary directory cannot be created. Check access rights");
            }
        }
        $file = md5($name);
        if (preg_match('#\b\w.{10,50}$#', $name, $m)) {
            $file = trim(preg_replace('#\W+#', '-', $m[0]), '-') . '-' . $file;
        }
        return $this->tempDirectory . '/' . $file . '.php';
    }

    /**
     * Registers run-time filter.
     * @param  string|NULL
     * @param  callable
     * @return self
     */
    public function addFilter($name, $callback)
    {
        if ($name == NULL) { // intentionally ==
            array_unshift($this->filters[NULL], $callback);
        } else {
            $this->filters[strtolower($name)] = $callback;
        }
        return $this;
    }

    /**
     * Returns all run-time filters.
     * @return callable[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Call a run-time filter.
     * @param  string  filter name
     * @param  array   arguments
     * @return mixed
     */
    public function invokeFilter($name, array $args)
    {
        $lname = strtolower($name);
        if (!isset($this->filters[$lname])) {
            $args2 = $args;
            array_unshift($args2, $lname);
            foreach ($this->filters[NULL] as $filter) {
                $res = call_user_func_array(Helpers::checkCallback($filter), $args2);
                if ($res !== NULL) {
                    return $res;
                } elseif (isset($this->filters[$lname])) {
                    return call_user_func_array(Helpers::checkCallback($this->filters[$lname]), $args);
                }
            }
            throw new \LogicException("Filter '$name' is not defined.");
        }
        return call_user_func_array(Helpers::checkCallback($this->filters[$lname]), $args);
    }

    /**
     * Adds new macro.
     * @return self
     */
    public function addMacro($name, IMacro $macro)
    {
        $this->getCompiler()->addMacro($name, $macro);
        return $this;
    }

    /**
     * ***************************************************
     * 2015-08-07 新添加类方法
     * 实现模版使用者可以在模版中使用自定义的函数标签
     * 如：{url('ModuleName:ControllerName:Action', [a => 1, b => 2])}
     * ***************************************************
     *
     * 添加latte模版支持用户自定义函数标签如 {url('', [a => 1, b => 2])}
     *
     * @param string $funcName  eg. 'url'
     * @param array  $funcArr   eg. ['router', $router, 'generateUrl']
     *
     * @return self
     *
     * @time 2015-08-06
     */
    public function addFunc($funcName, array $funcArr)
    {
        $func = $this->getCompiler()->addFunc($funcName, $funcArr);
        $this->userFuncs[$func[0]] = $func[1];
        return $this;
    }

    /**
     * 返回所有用户自定义的模版函数标签
     *
     * @return array
     */
    public function getAllUserFuncTags()
    {
        return $this->userFuncs;
    }

    /**
     * @return self
     */
    public function setContentType($type)
    {
        $this->contentType = $type;
        return $this;
    }

    /**
     * Sets path to temporary directory.
     * @return self
     */
    public function setTempDirectory($path)
    {
        $this->tempDirectory = $path;
        return $this;
    }

    /**
     * Sets auto-refresh mode.
     * @return self
     */
    public function setAutoRefresh($on = TRUE)
    {
        $this->autoRefresh = (bool) $on;
        return $this;
    }

    /**
     * @return Parser
     */
    public function getParser()
    {
        if (!$this->parser) {
            $this->parser = new Parser;
        }
        return $this->parser;
    }

    /**
     * @return Compiler
     */
    public function getCompiler()
    {
        if (!$this->compiler instanceof Compiler) {
            $this->compiler = new Compiler();
            Macros\CoreMacros::install($this->compiler);
            Macros\BlockMacros::install($this->compiler);
        }
        // ******************************************
        // 2015-01-17 改动，原为：
        //  $this->compiler = new Compiler;
        // ******************************************
        $this->templateParams && $this->compiler->setTemplateParams($this->templateParams);
        return $this->compiler;
    }

    /**
     * @return self
     */
    public function setLoader(ILoader $loader)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * @return ILoader
     */
    public function getLoader()
    {
        if (!$this->loader) {
            $this->loader = new Loaders\FileLoader;
        }
        return $this->loader;
    }

}

<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Config;

use Nopis\Lib\Config\Neon\Neon;

/**
 * @author wangbin
 */
class Loader implements \IteratorAggregate
{

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    protected $configurator;

    /**
     * @var array
     */
    protected $configs;

    /**
     * @var string
     */
    protected $cacheFilename = 'config.cache';

    /**
     * @var string
     */
    protected $cacheFile;

    /**
     * @var array
     */
    protected $cacheContents;

    /**
     * Load all config files
     *
     * @param array                                     $configs
     * @param \Nopis\Lib\Config\ConfiguratorInterface   $configurator
     */
    public function __construct(string $configs, ConfiguratorInterface $configurator)
    {
        $this->configurator = $configurator;
        $this->cacheFile = $this->configurator->getPubDir() . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $this->cacheFilename;
        $this->_loadConfig($configs);
    }

    /**
     * Realize Interface \IteratorAggregate
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->configs);
    }

    /**
     * Read and decode the content of config files
     *
     * @param string $configs
     * @throws \InvalidArgumentException
     */
    protected function _loadConfig(string $configs)
    {
        if ($this->isFresh())
            $this->configs = $this->getCaches();
        else {
            if (!is_readable($configs)) {
                throw new \InvalidArgumentException("File[{$configs}] is not exist or not readable");
            }
            $fileInfo = new \SplFileInfo($configs);

            $fileObj = $fileInfo->openFile('r');
            $content = '';
            while (!$fileObj->eof()) {
                $line = $fileObj->fgets();
                if (preg_match('/^(\s*)__import__:\s*([\w_-]+\.neon)\s*/i', $line, $m)) {
                    $import = $fileObj->getPath() . DIRECTORY_SEPARATOR . $m[2];
                    $content .= $this->loadImport($import, $m[1]);
                } else {
                    $content .= $line;
                }
            }

            try {
                $this->configs['config'] = Neon::parse($content);
                $this->configs['service'] = [
                    'parameters' => $this->configs['config']['parameters'],
                    'services' => $this->configs['config']['services']
                ];
                $this->putOriginalFile($fileObj->getFileInfo()->getPathname());
            } catch (\Exception $e) {
                throw new \Exception("File[{$configs}] parse error:{$e->getMessage()}");
            }

            $this->setCaches($this->configs, false);
        }
    }

    /**
     * Load import file in neon file
     *
     * @param string $import
     * @param string $spaces
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function loadImport($import, $spaces)
    {
        if (!is_readable($import)) {
            throw new \InvalidArgumentException("File[{$import}] is not exist or not readable");
        }

        $f = fopen($import, 'r+');
        $content = '';
        while (!feof($f)) {
            $content .= $spaces . fgets($f);
        }
        $this->putOriginalFile($import);

        return $content;
    }

    /**
     * Check the cache is fresh
     *
     * @return boolean
     */
    protected function isFresh()
    {
        if (!is_file($this->cacheFile))
            return false;

        if (false === ($cacheChangedTime = filemtime($this->cacheFile))) {
            return false;
        }

        foreach ($this->getOriginalFiles() as $f) {
            if ($cacheChangedTime <= filemtime($f))
                return false;
        }

        return true;
    }

    /**
     * Get all original file
     *
     * @return array
     */
    protected function getOriginalFiles()
    {
        $caches = $this->getCaches();
        return $caches[0];
    }

    /**
     * Put an original file into configs
     *
     * @param string $originalFiles
     */
    protected function putOriginalFile($originalFiles)
    {
        $this->configs[0][] = $originalFiles;
    }

    /**
     * Get cache which be saved in cache file
     *
     * @return array
     */
    protected function getCaches()
    {
        if (null === $this->cacheContents) {
            $this->cacheContents = include $this->cacheFile;
        }

        return $this->cacheContents;
    }

    /**
     * Save cache into cache file
     *
     * @param array $caches
     * @throws Exception
     */
    protected function setCaches(array $caches, $noCache = true)
    {
        if ($noCache)
            return;

        $cacheStr = "<?php\ndefined('DS') or die;\nreturn " . var_export($caches, true) . ";\n";
        if (!file_put_contents($this->cacheFile, $cacheStr)) {
            throw new \Exception('Unable to write config cache to cache file');
        }
    }

}

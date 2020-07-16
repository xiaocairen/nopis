<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class NopisAutoload
{

    /** @var string */
    private $srcDir = null;

    /** @var string */
    private $libDir = null;

    /** @var string */
    private $rootDir = null;

    /**
     * The autoload class
     *
     * @param array $loadDir  All NameSpace dir
     */
    public function __construct(array $loadDir)
    {
        $this->loadDir($loadDir);
        $this->rootDir = dirname(__DIR__);
    }

    public function registerAutoload($prepend = false)
    {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);
    }

    public function loadClass($class)
    {
        if (class_exists($class, false))
            return false;

        $class     = trim($class, '\\');
        $classfile = str_replace('\\', '/', $class);
        if (isset($this->libDir[$class[0]])) {
            foreach ($this->libDir[$class[0]] as $ns => $paths) {
                if (0 === strpos($class, $ns)) {
                    do {
                        $path = current($paths);
                        $file = $this->rootDir . $path . '/' . $classfile . '.php';
                        if (file_exists($file)) {
                            include $file;
                            return true;
                        }
                    } while (false !== next($paths));
                }
            }
        }

        foreach ($this->srcDir as $path) {
            $file = $this->rootDir . $path . '/' . $classfile . '.php';
            if (file_exists($file)) {
                include $file;
                return true;
            }
        }

        return false;
    }

    private function loadDir(array $loadDir)
    {
        $loadDir = (array) $loadDir;
        foreach ($loadDir as $ns => $paths) {
            $ns = trim($ns);
            if (empty($ns)) {
                $this->srcDir = (array) $paths;
            } else {
                $this->libDir[$ns[0]][$ns] = (array) $paths;
            }
        }
    }

}

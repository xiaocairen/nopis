<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Service;

use Exception;
use ReflectionClass;
use Nopis\Framework\Controller\Controller;

class ServiceDelegate
{

    /**
     * @var \ReflectionClass
     */
    private $refl;

    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var array
     */
    private $services;

    /**
     * @var \Nopis\Framework\Controller\Controller
     */
    private $controller;

    /**
     * Constructor.
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->refl = new ReflectionClass("\Service\ServiceDelegateInterface");

        foreach ($this->refl->getMethods() as $reflMethod) {
            $name = $reflMethod->getName();
            preg_match_all('/\*\s+@([a-z0-9_]+)\s+([^\\n]*)\\n/i', $reflMethod->getDocComment(), $matches);
            if (!in_array('return', $matches[1])) {
                throw new Exception('DocComment must has "return" at method ' . $name . ' in \Service\ServiceDelegateInterface');
            }

            foreach ($matches[1] as $k => $v) {
                if ($v == 'return') {
                    $this->methods[$name] = $this->normalizeClassName($matches[2][$k]);
                    break;
                }
            }
        }
    }

    public function __call($name, $arguments)
    {
        if (!isset($this->methods[$name])) {
            throw new Exception(sprintf('Not found method "%s" in \Service\ServiceDelegateInterface', $name));
        }

        if (!empty($arguments)) {
            return new $this->methods[$name](...$arguments);
        }
        if (!isset($this->services[$name])) {
            $this->services[$name] = new $this->methods[$name]();
        }
        return $this->services[$name];
    }

    private function normalizeClassName($classname)
    {
        return '\\' . trim(trim($classname), '\\');
    }

}

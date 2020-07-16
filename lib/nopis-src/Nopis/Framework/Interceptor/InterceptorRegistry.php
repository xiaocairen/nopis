<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Interceptor;

use Nopis\Lib\Config\ConfiguratorInterface;

/**
 * Description of InterceptorRegistry
 *
 * @author wb
 */
class InterceptorRegistry
{
    private $appChain;
    private $modChain;
    private $ctlChain;
    private $actChain;

    public function __construct(ConfiguratorInterface $configurator)
    {
        $this->registerInterceptor($this->loadRegistry($configurator));
    }

    /**
     * @return \Nopis\Framework\Interceptor\InterceptorExecutor
     */
    public function getExecutor()
    {
        return new InterceptorExecutor($this);
    }

    protected function loadRegistry(ConfiguratorInterface $configurator)
    {
        return $configurator->getConfig('framework.interceptors');
    }

    protected function registerInterceptor(array $registry)
    {
        if (!empty($registry['app'])) {
            $this->appChain = $registry['app'];
        }
        if (!empty($registry['module'])) {
            foreach ($registry['module'] as $m => $row) {
                $this->modChain[$m] = $row;
            }
        }
        if (!empty($registry['controller'])) {
            foreach ($registry['controller'] as $key => $row) {
                list($m, $c) = explode('.', $key);
                $this->ctlChain[$m][$c] = $row;
            }
        }
        if (!empty($registry['method'])) {
            foreach ($registry['method'] as $key => $row) {
                list($m, $c, $a) = explode('.', $key);
                $this->actChain[$m][$c][$a] = $row;
            }
        }
    }

    public function getExecutionChain($m, $c, $a)
    {
        return array_merge($this->appChain,
            isset($this->modChain[$m]) ? $this->modChain[$m] : [],
            isset($this->ctlChain[$m][$c]) ? $this->ctlChain[$m][$c] : [],
            isset($this->actChain[$m][$c][$a]) ? $this->actChain[$m][$c][$a] : []
        );
    }
}

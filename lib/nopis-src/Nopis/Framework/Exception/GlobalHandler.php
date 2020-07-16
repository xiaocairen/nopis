<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Exception;

use Exception;
use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\Http\Response;

/**
 * Description of GlobalHandler
 *
 * @author wangbin
 */
abstract class GlobalHandler
{
    /**
     * @var \Exception
     */
    protected $exception;

    /**
     * @var \Nopis\Lib\Http\RequestInterface
     */
    protected $request;

    /**
     * @var \Nopis\Lib\Config\ConfiguratorInterface
     */
    protected $configurator;

    /**
     * @var \ReflectionParameter[]
     */
    private $publicMethods = [];

    final public function __construct(Exception $e, RequestInterface $request, ConfiguratorInterface $configurator)
    {
        $this->exception = $e;
        $this->request = $request;
        $this->configurator = $configurator;
        $refl = new \ReflectionObject($this);
        $methods = $refl->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $parameters = $method->getParameters();
            if (1 != count($parameters)) {
                continue;
            }
            $type = $parameters[0]->getType();
            if (null == $type) {
                continue;
            }

            $this->publicMethods[$method->getName()] = $type->getName();
        }
    }

    final public function handleException()
    {
        $exceptionClass = get_class($this->exception);
        foreach ($this->publicMethods as $method => $handleException) {
            if ($exceptionClass == $handleException) {
                return $this->$method($this->exception);
            }
        }
        return null;
    }

    protected function doResponse(\Exception $ex)
    {
        $return['success']    = false;
        $return['error_code'] = $ex->getCode();
        $return['error_msg']  = $ex->getMessage();

        $response = new Response();
        $response->getHeaders()->setContentType('application/json');
        return $response->setContent(json_encode($return, JSON_UNESCAPED_UNICODE));
    }
}

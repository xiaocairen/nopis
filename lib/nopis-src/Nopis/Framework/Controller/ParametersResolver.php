<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Controller;

/**
 * Description of ParamtersResolver
 *
 * @author wangbin
 */
class ParametersResolver
{

    /**
     * @var \Nopis\Framework\Controller\Controller
     */
    private $controller;

    /**
     * @var \ReflectionMethod
     */
    private $reflection;

    /**
     * @var string
     */
    private $method;

    public function __construct(Controller $controller, string $method)
    {
        $this->controller = $controller;
        $this->reflection = new \ReflectionMethod($controller, $method);
        $this->method = $method;
    }

    public function getParameters()
    {
        $refParameters = $this->reflection->getParameters();
        if (!$refParameters) {
            return [];
        }

        $parameters = [];
        foreach ($refParameters as $ref) {
            $name = $ref->getName();
            $type = $ref->getType();
            $optional = $ref->isOptional();
            if (null == $type) {
                $parameters[] = $this->controller->request->getRequest($name, $optional ? $ref->getDefaultValue() : null);
                continue;
            }

            $tname = $type->getName();
            switch ($tname) {
                case 'bool':
                    $bool = $this->controller->request->getRequest($name, $optional ? $ref->getDefaultValue() : false);
                    $parameters[] = false === $bool ? false : !in_array(strtolower($bool), ['0', 'false', 'null', '']);
                    break;
                case 'int':
                    $parameters[] = (int)$this->controller->request->getRequest($name, $optional ? $ref->getDefaultValue() : 0);
                    break;
                case 'float':
                    $parameters[] = (float)$this->controller->request->getRequest($name, $optional ? $ref->getDefaultValue() : 0);
                    break;
                case 'string':
                    $parameters[] = $this->controller->request->getRequest($name, $optional ? $ref->getDefaultValue() : '');
                    break;
                case 'array':
                    $parameters[] = (array)$this->controller->request->getRequest($name, $optional ? $ref->getDefaultValue() : array());
                    break;
                default:
                    if (is_subclass_of($tname, 'Nopis\Lib\Entity\DTO')) {
                        $value = $this->controller->request->getRequest($name, []);
                        $dto_object = $this->resolveDto($tname, is_array($value) ? $value : [], true);
                        null != $dto_object && $parameters[] = $dto_object;
                    } elseif ($tname == 'Nopis\Lib\Http\RequestInterface' || is_subclass_of($tname, 'Nopis\Lib\Http\RequestInterface')) {
                        $parameters[] = $this->controller->request;
                    } elseif ($tname == 'Nopis\Lib\Http\ResponseInterface' || is_subclass_of($tname, 'Nopis\Lib\Http\ResponseInterface')) {
                        $parameters[] = $this->controller->response;
                    } else {
                        throw new \Exception(sprintf('type of parameter \'%s\' of method \'%s\' must be in [bool, int, float, string, array, DTO, RequestInterface, ResponseInterface]', $name, $this->method));
                    }
                    break;
            }
        }

        return $parameters;
    }

    private function resolveDto(string $dto_name, array $query_value, bool $top_dto = false)
    {
        $dto = new $dto_name();
        $properties = (new \ReflectionObject($dto))->getProperties(\ReflectionProperty::IS_PUBLIC);
        $property_names = [];
        foreach ($properties as $property) {
            $property_names[] = $property->getName();
        }

        if ($query_value) {
            if (!array_intersect($property_names, array_keys($query_value))) {
                return null;
            }

            foreach ($properties as $property) {
                $property_name = $property->getName();
                $doc_comment = $property->getDocComment();
                if (!$doc_comment || !preg_match('/\s*@var\s+([^\\n]+)\s*\\n/i', $doc_comment, $matches) || count($matches) != 2) {
                    $dto->$property_name = isset($query_value[$property_name]) ? $query_value[$property_name] : null;
                    continue;
                }

                $tname = trim($matches[1]);
                switch (strtolower($tname)) {
                    case 'boolean':
                    case 'bool':
                        $dto->$property_name = isset($query_value[$property_name]) ? !in_array(strtolower($query_value[$property_name]), ['0', 'false', 'null', '']) : false;
                        break;
                    case 'int':
                        $dto->$property_name = isset($query_value[$property_name]) ? (int)$query_value[$property_name] : 0;
                        break;
                    case 'float':
                        $dto->$property_name = isset($query_value[$property_name]) ? (float)$query_value[$property_name] : 0;
                        break;
                    case 'string':
                        $dto->$property_name = isset($query_value[$property_name]) ? $query_value[$property_name] : '';
                        break;
                    case 'array':
                        $dto->$property_name = isset($query_value[$property_name]) ? (array)$query_value[$property_name] : [];
                        break;
                    default:
                        $is_arr = false;
                        if ('[]' == substr($tname, -2)) {
                            $is_arr = true;
                            $tname = substr($tname, 0, -2);
                        }
                        if (!is_subclass_of($tname, 'Nopis\Lib\Entity\DTO')) {
                            throw new \Exception(sprintf('property \'%s\' of class \'%s\' is not subclass of \Nopis\Lib\Entity\DTO', $property_name, $dto_name));
                        }

                        if (!$is_arr) {
                            $dto_object = $this->resolveDto($tname, is_array($query_value[$property_name]) ? $query_value[$property_name] : []);
                            null != $dto_object && $dto->$property_name = $dto_object;
                            break;
                        }

                        if (!isset($query_value[$property_name]) || !is_array($query_value[$property_name])) {
                            $dto->$property_name = [];
                            break;
                        }

                        foreach ($query_value[$property_name] as $value) {
                            if (!is_array($value))
                                continue;
                            $dto_object = $this->resolveDto($tname, $value);
                            null != $dto_object && $dto->$property_name[] = $dto_object;
                        }
                        break;
                }
            }
        } elseif ($top_dto) {
            foreach ($properties as $property) {
                $property_name = $property->getName();
                $doc_comment = $property->getDocComment();
                if (!$doc_comment || !preg_match('/\s*@var\s+([^\\n]+)\s*\\n/i', $doc_comment, $matches) || count($matches) != 2) {
                    $dto->$property_name = $this->controller->request->getRequest($property_name, null);
                    continue;
                }

                $tname = trim($matches[1]);
                switch (strtolower($tname)) {
                    case 'boolean':
                    case 'bool':
                        $bool = $this->controller->request->getRequest($property_name, false);
                        $dto->$property_name = false === $bool ? false : !in_array(strtolower($bool), ['0', 'false', 'null', '']);
                        break;
                    case 'int':
                        $dto->$property_name = (int)$this->controller->request->getRequest($property_name, 0);
                        break;
                    case 'float':
                        $dto->$property_name = (float)$this->controller->request->getRequest($property_name, 0);
                        break;
                    case 'string':
                        $dto->$property_name = $this->controller->request->getRequest($property_name, '');
                        break;
                    case 'array':
                        $dto->$property_name = (array)$this->controller->request->getRequest($property_name, array());
                        break;
                    default:
                        $is_arr = false;
                        if ('[]' == substr($tname, -2)) {
                            $is_arr = true;
                            $tname = substr($tname, 0, -2);
                        }
                        if (!is_subclass_of($tname, 'Nopis\Lib\Entity\DTO')) {
                            throw new \Exception(sprintf('property \'%s\' of class \'%s\' is not subclass of \Nopis\Lib\Entity\DTO', $property_name, $dto_name));
                        }

                        $values = $this->controller->request->getRequest($property_name, []);
                        if (!$is_arr) {
                            $dto_object = $this->resolveDto($tname, is_array($values) ? $values : []);
                            null != $dto_object && $dto->$property_name = $dto_object;
                            break;
                        }

                        if (!isset($values) || !is_array($values)) {
                            $dto->$property_name = [];
                            break;
                        }

                        foreach ($values as $value) {
                            if (!is_array($value))
                                continue;
                            $dto_object = $this->resolveDto($tname, $value);
                            null != $dto_object && $dto->$property_name[] = $dto_object;
                        }
                        break;
                }
            }
        } else {
            return null;
        }

        return $dto;
    }
}

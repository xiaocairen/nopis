<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\DI;

use ReflectionClass;

/**
 * @author wangbin
 */
class Builder implements BuilderInterface
{
    /* Define some key words in Service file */
    const D_KEY_CLASS          = 'className';
    const D_KEY_METHOD         = 'methodName';
    const D_KEY_METHOD_PARAM   = 'arguments';
    const D_INJECT_CONSTRUCTOR = 'arguments';
    const D_INJECT_SETTER      = 'callMethods';
    const D_INJECT_PROPERTY    = 'properties';
    const D_REFERENCE_PARAM    = '@parameter:';
    const D_REFERENCE_CLASS    = '@class:';
    const D_REFERENCE_SERVICE  = '@service:';
    const D_REFERENCE_CONFIG   = '@config:';

    /* Define some Exception message */
    const M_UNDEFINED_SERVICE       = 'Service \'%s\' is undefined';
    const M_UNDEFINED_CLASS         = 'Class \'%s\' is undefined';
    const M_UNDEFINED_METHODNAME    = 'The method name of Service \'%s\' has no define';
    const M_NOT_FOUND_CLASSNAME     = '[Service:%s] ClassName not found';
    const M_NOT_FOUND_PARAM         = '[Service:%s] Parameter \'%s\' not found';
    const M_NOT_FOUND_PROPERTY      = '[Service:%s] Property name \'%s\' not found';
    const M_NOT_FOUND_METHOD        = '[Service:%s] Method name \'%s\' not found';
    const M_NOT_FOUND_SINGLETON     = '[Service:%s] Not found method or method arguments in singleton class';
    const M_NOT_CALLABLE_METHOD     = "[Service:%s] \"%s::%s\" is not callable";
    const M_NON_PUBLIC_PROPERTY     = '[Service:%s] Property \'%s\' is not public';
    const M_NON_PUBLIC_METHOD       = '[Service:%s] Method \'%s\' is not public';
    const M_NON_PRIVATE_CONSTRUCTOR = "[Service:%s] Constructor is not private in class %s";
    const M_NON_PUBLIC_CONSTRUCTOR  = "Cannot instantiate protected/private constructor in class %s";
    const M_MAKE_FAILURE            = "Could not make %s: %s";
    const M_NEEDS_DEFINITION        = "Injection definition required for non-concrete parameter $%s of type %s";

    /**
     * @var \Nopis\Lib\DI\Definition
     */
    protected $definition;

    /**
     * @var array
     */
    protected $storage = [];

    public function __construct(Definition $definition)
    {
        $this->definition = $definition;
    }

    /**
     * Make an Object
     *
     * @param string $classIdentifier
     * @param array  $temporaryInjectionArgs Temporary injection arguments at call-time, nonsupport recursive parameter
     * @return Object
     */
    public function make($classIdentifier, array $temporaryInjectionArgs = [])
    {
        $storageKey = md5($classIdentifier);
        if (isset($this->storage[$storageKey]) && empty($temporaryInjectionArgs)) {
            $obj = $this->storage[$storageKey];
        } elseif (null === ($definition = $this->definition->getService($classIdentifier))) {
            // instance class
            if (class_exists($classIdentifier)) {
                $obj = $this->provisionInstance(new ReflectionClass($classIdentifier), $classIdentifier, $temporaryInjectionArgs);
                empty($temporaryInjectionArgs) && $this->storage[$storageKey] = $obj;
            } else {
                throw new DefinitionException(
                    sprintf(self::M_UNDEFINED_SERVICE, $classIdentifier)
                );
            }
        } else {
            // instance service
            $this->resolveServiceDefinition($definition, $classIdentifier, $temporaryInjectionArgs);

            $refl = new ReflectionClass($definition[self::D_KEY_CLASS]);
            if (!$refl->isInstantiable()) {
                if (isset($definition[self::D_INJECT_SETTER])
                        && isset($definition[self::D_INJECT_SETTER][self::D_KEY_METHOD])
                        && isset($definition[self::D_INJECT_SETTER][self::D_KEY_METHOD_PARAM])) {
                    $obj = $this->makeSingleton($classIdentifier, $temporaryInjectionArgs);
                } else {
                    $type = $refl->isInterface() ? 'interface' : 'abstract';
                    throw new DefinitionException(
                        sprintf(self::M_NEEDS_DEFINITION, $type, $definition[self::D_KEY_CLASS])
                    );
                }
            } else {
                $obj = $this->provisionServiceInstance($refl, $classIdentifier, $definition);
                empty($temporaryInjectionArgs) && $this->storage[$storageKey] = $obj;
            }
        }

        return $obj;
    }

    /**
     * Make a singleton object
     *
     * @param string $classIdentifier
     * @param array  $temporaryInjectionArgs Temporary injection arguments at first call-time, nonsupport recursive parameter
     *
     * @return Object
     */
    public function makeSingleton($classIdentifier, array $temporaryInjectionArgs = [])
    {
        if (null === ($definition = $this->definition->getService($classIdentifier))) {
                throw new DefinitionException(
                    sprintf(self::M_UNDEFINED_SERVICE, $classIdentifier)
                );
        }
        // instance service
        $this->resolveServiceDefinition($definition, $classIdentifier);

        if (!isset($definition[self::D_INJECT_SETTER])
                || !isset($definition[self::D_INJECT_SETTER][self::D_KEY_METHOD])
                || !isset($definition[self::D_INJECT_SETTER][self::D_KEY_METHOD_PARAM])) {
            throw new DefinitionException(
                sprintf(self::M_NOT_FOUND_SINGLETON, $classIdentifier)
            );
        }

        if ($temporaryInjectionArgs) {
            $definition[self::D_INJECT_SETTER][self::D_KEY_METHOD_PARAM] = array_merge($definition[self::D_INJECT_SETTER][self::D_KEY_METHOD_PARAM], $temporaryInjectionArgs);
        }

        $singletonClass = $definition[self::D_KEY_CLASS];
        $instanceFunc   = $definition[self::D_INJECT_SETTER][self::D_KEY_METHOD];
        $definedArgs    = $definition[self::D_INJECT_SETTER][self::D_KEY_METHOD_PARAM];

        if (!is_callable([$singletonClass, $instanceFunc])) {
            throw new DefinitionException(
                sprintf(self::M_NOT_CALLABLE_METHOD, $classIdentifier, $singletonClass, $instanceFunc)
            );
        }

        $refl = new \ReflectionClass($singletonClass);
        $ctor = $refl->getConstructor();
        if (!$ctor->isPrivate()) {
            throw new DefinitionException(
                sprintf(self::M_NON_PRIVATE_CONSTRUCTOR, $classIdentifier, $singletonClass)
            );
        }

        $reflMethod = $refl->getMethod($instanceFunc);
        $args = $this->provisionFuncArgs($reflMethod, $definedArgs, $reflMethod->getParameters());

        return $reflMethod->invokeArgs(NULL, $args);
    }

    /**
     * Return the service definition
     *
     * @param array $definition
     * @param string $classIdentifier
     * @param array  $temporaryInjectionArgs Temporary injection arguments at call-time
     *
     * @return array
     * @throws DefinitionException
     */
    protected function resolveServiceDefinition(array & $definition, $classIdentifier, array $temporaryInjectionArgs = [])
    {
        if (!isset($definition[self::D_KEY_CLASS])) {
            throw new DefinitionException(
                sprintf(self::M_NOT_FOUND_CLASSNAME, $classIdentifier)
            );
        }

        if (0 === strpos($definition[self::D_KEY_CLASS], self::D_REFERENCE_PARAM)) {
            $parameterName = substr($definition[self::D_KEY_CLASS], strlen(self::D_REFERENCE_PARAM));
            $class = $this->definition->getParameter($parameterName);
            if (null === $class) {
                throw new DefinitionException(
                    sprintf(self::M_NOT_FOUND_PARAM, $classIdentifier, $parameterName)
                );
            }

            $definition[self::D_KEY_CLASS] = $class;
        }

        if ($temporaryInjectionArgs) {
            isset($definition[self::D_KEY_METHOD_PARAM]) || $definition[self::D_KEY_METHOD_PARAM] = [];
            $definition[self::D_KEY_METHOD_PARAM] = array_merge($definition[self::D_KEY_METHOD_PARAM], $temporaryInjectionArgs);
        }
    }

    private function provisionServiceInstance(ReflectionClass $refl, $classIdentifier, array $definition)
    {
        $definedArgs = isset($definition[self::D_INJECT_CONSTRUCTOR]) ? $definition[self::D_INJECT_CONSTRUCTOR] : [];
        $obj = $this->provisionInstance($refl, $definition[self::D_KEY_CLASS], $definedArgs);

        if (empty($definedArgs)) {
            if (isset($definition[self::D_INJECT_SETTER])) {
                $this->callSetters($obj, $definition[self::D_INJECT_SETTER], $classIdentifier);
            } elseif (isset($definition[self::D_INJECT_PROPERTY])) {
                $this->callProperties($obj, $definition[self::D_INJECT_PROPERTY], $classIdentifier);
            }
        }

        return $obj;
    }

    private function provisionInstance(ReflectionClass $refl, $class, array $definedArgs = [])
    {
        try {
            $ctor = $refl->getConstructor();
            if (null === $ctor) {
                $obj = new $class;
            } elseif (!$ctor->isPublic()) {
                throw new DefinitionException(
                    sprintf(self::M_NON_PUBLIC_CONSTRUCTOR, $class)
                );
            } elseif (($ctorParams = $ctor->getParameters())) {
                $ctorArgs = $this->provisionFuncArgs($ctor, $definedArgs, $ctorParams);
                $obj = $refl->newInstanceArgs($ctorArgs);
            } else {
                $obj = new $class;
            }

            return $obj;

        } catch (\ReflectionException $e) {
            throw new DefinitionException(
                sprintf(self::M_MAKE_FAILURE, $class, $e->getMessage()), 0, $e
            );
        }
    }

    private function provisionFuncArgs(\ReflectionMethod $reflMethod, array $definedArgs, array $reflParams)
    {
        $funcArgs = array();
        $reflParams = isset($reflParams) ? $reflParams : $reflMethod->getParameters();

        foreach ($reflParams as $reflParam) {
            $name = $reflParam->name;

            if (isset($definedArgs[$name])) {
                $arg = is_object($definedArgs[$name]) ? $definedArgs[$name] : $this->buildArgFromDefinition($definedArgs[$name]);
            } else {
                $arg = $this->buildArgFromTypeHint($reflParam);
            }

            $funcArgs[] = $arg;
        }

        return $funcArgs;
    }

    private function buildArgFromDefinition($definedArg)
    {
        if (is_array($definedArg)) {
            foreach ($definedArg as $k => $r) {
                $arg[$k] = $this->buildArgFromDefinition($r);
            }
        } elseif (0 === strpos($definedArg, self::D_REFERENCE_CLASS)) {
            // @class:
            $class = substr($definedArg, strlen(self::D_REFERENCE_CLASS));
            if (!class_exists($class)) {
                throw new DefinitionException(
                    sprintf(self::M_UNDEFINED_CLASS, $class)
                );
            }
            $arg = $this->make($class);
        } elseif (0 === strpos($definedArg, self::D_REFERENCE_SERVICE)) {
            // @service:
            $service = substr($definedArg, strlen(self::D_REFERENCE_SERVICE));
            $arg = $this->make($service);
        } elseif (0 === strpos($definedArg, self::D_REFERENCE_CONFIG)) {
            // @config:
            $configKey = substr($definedArg, strlen(self::D_REFERENCE_CONFIG));
            $arg = $this->definition->getConfig($configKey);
        } else {
            $arg = $definedArg;
        }

        return $arg;
    }

    private function buildArgFromTypeHint(\ReflectionParameter $reflParam) {
        $reflClass = $reflParam->getClass();
        if ($reflClass instanceof \ReflectionClass) {
            $typeHint = $reflClass->getName();
            if (!class_exists($typeHint)) {
                throw new DefinitionException(
                    sprintf(self::M_UNDEFINED_CLASS, $typeHint)
                );
            }
            $arg = $this->make($typeHint);
        } elseif ($reflParam->isDefaultValueAvailable()) {
            $arg = $reflParam->getDefaultValue();
        } else {
            $arg = null;
        }

        return $arg;
    }

    private function callSetters($obj, array $setters, $classIdentifier)
    {
        foreach ($setters as $setter) {
            if (!isset($setter[self::D_KEY_METHOD])) {
                throw new DefinitionException(
                    sprintf(self::M_UNDEFINED_METHODNAME, $classIdentifier)
                );
            } elseif (!method_exists($obj, $setter[self::D_KEY_METHOD])) {
                throw new DefinitionException(
                    sprintf(self::M_NOT_FOUND_METHOD, $classIdentifier, $propertyName)
                );
            }

            $reflMethod = new \ReflectionMethod($obj, $setter[self::D_KEY_METHOD]);
            if (!$reflMethod->isPublic()) {
                throw new DefinitionException(
                    sprintf(self::M_NON_PUBLIC_METHOD, $classIdentifier, $propertyName)
                );
            }

            $reflParams = $reflMethod->getParameters();
            if (!empty($reflParams)) {
                $definedArgs = isset($setter[self::D_KEY_METHOD_PARAM]) ? $setter[self::D_KEY_METHOD_PARAM] : [];
                $args = $this->provisionFuncArgs($reflMethod, $definedArgs, $reflParams);
                $reflMethod->invokeArgs($obj, $args);
            } else {
                $reflMethod->invoke($obj);
            }
        }
    }

    private function callProperties($obj, array $properties, $classIdentifier)
    {
        foreach ($properties as $propertyName => $definition) {
            if (!property_exists($obj, $propertyName)) {
                throw new DefinitionException(
                    sprintf(self::M_NOT_FOUND_PROPERTY, $classIdentifier, $propertyName)
                );
            }

            $reflProperty = new \ReflectionProperty($obj, $propertyName);
            if (!$reflProperty->isPublic()) {
                throw new DefinitionException(
                    sprintf(self::M_NON_PUBLIC_PROPERTY, $classIdentifier, $propertyName)
                );
            }

            if (is_array($definition)) {
                $reflProperty->setValue($obj, $definition);
            } elseif (0 === strpos($definition, self::D_REFERENCE_CLASS)) {
                $class = substr($definition, strlen(self::D_REFERENCE_CLASS));
                if (!class_exists($class)) {
                    throw new DefinitionException(
                        sprintf(self::M_UNDEFINED_CLASS, $class)
                    );
                }
                $reflProperty->setValue($obj, $this->make($service));
            } elseif (0 === strpos($definition, self::D_REFERENCE_SERVICE)) {
                $service = substr($definition, strlen(self::D_REFERENCE_SERVICE));
                $reflProperty->setValue($obj, $this->make($service));
            } else {
                $reflProperty->setValue($obj, $definition);
            }
        }
    }
}

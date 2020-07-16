<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Entity;

use ReflectionClass;
use ReflectionProperty;
use Nopis\Lib\Entity\ValueObject;
use nPub\SPI\Persistence\Entity\Exceptions\DefinitionException;
use nPub\SPI\Persistence\Entity\Exceptions\PropertyNotFoundException;

/**
 * Description of CreateHelper
 *
 * @author wangbin
 */
abstract class CreateHelper implements HelperInterface
{

    /**
     * @var \Nopis\Lib\Entity\ValueObject
     */
    protected $entity;

    /**
     * @var \ReflectionClass
     */
    protected $reflClass;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var \nPub\SPI\Persistence\Entity\FieldValidator
     */
    protected $validator;

    /**
     * @var array
     */
    protected $docComments = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->validator = new FieldValidator();
    }

    /**
     * Set field value
     *
     * @param string $fieldIdentifier
     * @param mixed $value
     */
    public function setField($fieldIdentifier, $value)
    {
        $this->fields[$fieldIdentifier] = $value;
    }

    /**
     * Set a list fields value.
     *
     * @param array $fieldValues
     */
    public function setFields(array $fieldValues)
    {
        $this->fields = $this->fields !== null ? array_merge($this->fields, $fieldValues) : $fieldValues;
    }

    /**
     * Get entity object
     *
     * @throws \Exception
     * @return \Nopis\Lib\Entity\ValueObject
     */
    public function getEntity()
    {
        if (!$this->entity instanceof ValueObject) {
            if (empty($this->fields)) {
                throw new \Exception(sprintf('All fields of entity "%s" is empty', __CLASS__));
            }

            $this->setEntity($this->fields);
            if (!$this->entity instanceof ValueObject) {
                throw new \Exception(sprintf('Not found initialized Entity Object, in %s::setEntity()', __CLASS__));
            }
            $this->validate();
        }

        return $this->entity;
    }

    /**
     * Get an creation array, eg. field => value
     *
     * @return array
     */
    public function getCreationFieldsValues()
    {
        $this->getEntity();

        $fields = array();
        foreach ($this->reflClass->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED) as $property) {
            $propertyName = $property->getName();
            $docComment = $this->getDocComment($propertyName);
            if (isset($docComment['primary'])) {
                if ('primary' == $docComment['primary']) {
                    continue;
                }
                elseif ('unique' == $docComment['primary']) {
                    throw new \Exception('has unique index field \'' . $propertyName . '\', can not use getCreationFieldsValues method');
                }
            }

            $fields[$propertyName] = $this->entity->getPropertyValue($propertyName);
        }

        return $fields;
    }

    /**
     * Get property's doc comments.
     *
     * @param string $property
     * @return array
     * @throws \nPub\SPI\Persistence\Entity\Exceptions\PropertyNotFoundException
     */
    public function getDocComment($property)
    {
        if (!property_exists($this->entity, $property)) {
            throw new PropertyNotFoundException($property, get_class($this->entity));
        }

        return $this->docComments[$property];
    }

    /**
     * Validate field's value
     */
    protected function validate()
    {
        $this->reflClass = new ReflectionClass($this->entity);
        foreach ($this->reflClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $docComment = $this->reflClass->getProperty($propertyName)->getDocComment();
            if (!$docComment) {
                throw new DefinitionException(
                    sprintf('The field "%s" docComment is empty', $propertyName),
                    get_class($this->entity)
                );
            }

            preg_match_all('/\*\s+@([a-z0-9_]+)\s+([^\\n]*)\\n/i', $docComment, $matches);

            foreach ($matches[1] as $key => $method) {
                $method = trim($method);
                $param = trim($matches[2][$key]);
                if ('var' == $method)
                    continue;

                if (!method_exists($this->validator, $method)) {
                    throw new DefinitionException(
                        sprintf('The field "%s" docComment method "@%s" not be found in class "%s"', $propertyName, $method, get_class($this->validator)),
                        get_class($this->entity)
                    );
                }

                if (null !== ($propertyValue = $this->entity->getPropertyValue($propertyName))) {
                    $propertyValue = trim($propertyValue);
                }

                $this->docComments[$propertyName][$method] = $param;
                $this->validator->$method($this, $param, $propertyName, $propertyValue, isset($this->fields[$propertyName]));
            }
        }
    }

    /**
     * Set entity object
     *
     * @param array $arguments  entity's arguments
     */
    abstract protected function setEntity(array $arguments);
}

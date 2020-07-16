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
 * Description of UpdateHelper
 *
 * @author wangbin
 */
abstract class UpdateHelper implements HelperInterface
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
     * @var \nPub\SPI\Persistence\Entity\FieldValidator
     */
    protected $validator;

    /**
     * @var array
     */
    protected $docComments = [];

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\Entity\ValueObject $valueObject
     */
    public function __construct(ValueObject $valueObject)
    {
        $this->entity    = $valueObject;
        $this->reflClass = new ReflectionClass($this->entity);
        $this->validator = new FieldValidator();

        foreach ($this->entity as $field => $value) {
            $this->validate($this->reflClass->getProperty($field), $field, $value);
        }
    }

    /**
     * Set field value
     *
     * @param string $fieldIdentifier
     * @param mixed $value
     */
    public function setField($fieldIdentifier, $value)
    {
        $this->entity->$fieldIdentifier = $value;
        $this->validate($this->reflClass->getProperty($fieldIdentifier), $fieldIdentifier, $value);
    }

    /**
     * Set a list fields value.
     *
     * @param array $fieldValues
     */
    public function setFields(array $fieldValues)
    {
        foreach ($fieldValues as $field => $value) {
            $this->entity->$field = $value;
            $this->validate($this->reflClass->getProperty($field), $field, $value);
        }
    }

    /**
     * Get an updation array, eg. field => value
     *
     * @return array
     */
    public function getUpdationFieldsValues()
    {
        $fields = array();
        foreach ($this->reflClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $propertyName = $property->getName();
            $docComment = $this->getDocComment($propertyName);
            if (!$docComment)
                continue;

            if ('public' !== $docComment['access'] || (isset($docComment['primary']) && 'primary' == $docComment['primary']))
                continue;
            if (isset($docComment['primary']) && 'unique' == $docComment['primary']) {
                throw new \Exception('has unique index field \'' . $propertyName . '\', can not use getUpdationFieldsValues method');
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

        return isset($this->docComments[$property]) ? $this->docComments[$property] : false;
    }

    /**
     * Validate field's value
     *
     * @param ReflectionProperty $property
     * @param string $field
     * @param mixed $value
     * @throws DefinitionException
     */
    protected function validate(ReflectionProperty $property, $field, $value)
    {
        $docComment = $property->getDocComment();
        if (!$docComment) {
            throw new DefinitionException(
                sprintf('The field "%s" docComment is empty', $field),
                get_class($this->entity)
            );
        }

        preg_match_all('/\*\s+@([a-z0-9_]+)\s+([^\\n]*)\\n/i', $docComment, $matches);

        foreach ($matches[1] as $key => $method) {
            $method = trim($method);
            $param = trim($matches[2][$key]);
            if ($method === 'var')
                continue;

            if (!method_exists($this->validator, $method)) {
                throw new DefinitionException(
                    sprintf('The field "%s" docComment method "@%s" not be found in class "%s"', $field, $method, get_class($this->validator)),
                    get_class($this->entity)
                );
            }

            $this->docComments[$property->getName()][$method] = $param;
            //$this->validator->$method($this, $param, $field, $value, true);
        }
    }
}

<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Content;

use Exception;

/**
 * @author wangbin
 */
class PropertyHelper
{

    /**
     * @var \nPub\SPI\Persistence\Content\SPIContentInterface
     */
    private $spiContent;

    /**
     * @var array
     */
    private $properties;

    /**
     * @var array
     */
    private $assistants;

    /**
     * @var array
     */
    private $assistKeys;

    /**
     * Contructor.
     *
     * @param \nPub\SPI\Persistence\Content\SPIContentInterface $SPIContent
     * @param array $properties
     */
    public function __construct(SPIContentInterface $SPIContent, array $properties)
    {
        $this->spiContent = $SPIContent;
        $this->properties = $properties;
        $this->assistants = $this->spiContent->getAssistants();
        $this->assistKeys = is_array($this->assistants) && !empty($this->assistants) ? array_keys($this->assistants) : [];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function resovleProperties()
    {
        foreach ($this->properties as $property => $value) {
            if (!property_exists($this->spiContent, $property)) {
                unset($this->properties[$property]);
                continue;
            }

            if (in_array($property, $this->assistKeys)) {
                $className = $this->resovleClassName($this->assistants[$property]['class']);
                switch ($this->assistants[$property]['type']) {
                    case SPIContentInterface::ONE_ONE:
                        $this->spiContent->$property = $this->resovleAssist($value, $property, $className, SPIContentInterface::ONE_ONE);
                        break;

                    case SPIContentInterface::ONE_MANY:
                        $this->spiContent->$property = [];
                        foreach ($value as $v) {
                            array_push($this->spiContent->$property, $this->resovleAssist($v, $property, $className, SPIContentInterface::ONE_MANY));
                        }
                        break;

                    default:
                        throw new Exception('Unsupport map type in SPIContent at ' . $property);
                }
                unset($this->properties[$property]);
                continue;
            }

            is_array($value) && $this->properties[$property] = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $this->properties;
    }

    /**
     * @param mixed $value
     * @param string $property
     * @param string $className
     * @param int $type
     * @return Object
     * @throws Exception
     */
    private function resovleAssist($value, $property, $className, $type)
    {
        if (null === $value)
            return null;

        if (!is_array($value) && !is_object($value)) {
            if ($type == SPIContentInterface::ONE_ONE) {
                throw new Exception(sprintf('Property %s expect an array or an Object of %s, %s given.', $property, $className, gettype($value)));
            } else {
                throw new Exception(sprintf('Property %s expect a two-dimensional array or an Object of %s, %s given.', $property, $className, gettype($v)));
            }
        }
        if (is_object($value) && !$value instanceof $className) {
            throw new Exception(sprintf('Property %s expect an Object of %s, %s given.', $property, $className, get_class($value)));
        }
        return is_array($value) ? new $className($value) : $value;
    }

    /**
     * Format class name
     *
     * @param string $className
     * @return string
     */
    private function resovleClassName($className)
    {
        return '\\' . trim(trim($className), '\\');
    }
}

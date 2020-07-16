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
 * Description of AssistHelper
 *
 * @author wb
 */
class AssistHelper
{

    /**
     * @var \nPub\SPI\Persistence\Content\SPIContent
     */
    private $SPIContent;

    /**
     * @var array
     */
    private $assistants;

    /**
     * @var boolean
     */
    private $hasAssistants = false;

    /**
     * @var array
     */
    private $assistNames;

    /**
     * @var \nPub\SPI\Persistence\Content\SPIContent[]
     */
    private $assistObjects;

    /**
     * Constructor.
     *
     * @param \nPub\SPI\Persistence\Content\SPIContent $content
     */
    public function __construct(SPIContent $content)
    {
        $this->SPIContent = $content;
        $this->assistants = $this->SPIContent->getAssistants() ?: [];

        foreach ($this->getAssistNames() as $assistName) {
            if (!property_exists($this->SPIContent, $assistName)) {
                throw new Exception(
                    sprintf('Property "%s" not found in class "%s"', $assistName, get_class($this->SPIContent))
                );
            }

            $assistVal = $this->SPIContent->getPropertyValue($assistName);
            if ($assistVal && (is_array($assistVal) || is_object($assistVal))) {
                $className = $this->getClassName($assistName);
                switch ($this->getMapType($assistName)) {
                    case SPIContent::ONE_ONE:
                        if (!$assistVal instanceof $className)
                            throw new Exception(sprintf('\'%s\' is not instanceof \'%s\'', get_class($assistVal), $className));
                        break;

                    case SPIContent::ONE_MANY:
                        if (!is_array($assistVal))
                            throw new Exception;
                        foreach ($assistVal as $row) {
                            if (!$row instanceof $className)
                                throw new Exception(sprintf('\'%s\' is not instanceof \'%s\'', get_class($row), $className));
                        }
                        break;

                    default:
                        throw new Exception('Unsupport map type in SPIContent at ' . $assistName);
                }

                $this->hasAssistants = true;
            }
        }
    }

    /**
     * @return boolean
     */
    public function hasAssistants()
    {
        return $this->hasAssistants;
    }

    /**
     * @return array
     */
    public function getAssistNames()
    {
        if (null === $this->assistNames)
            $this->assistNames = is_array($this->assistants) && !empty($this->assistants) ? array_keys($this->assistants) : [];

        return $this->assistNames;
    }

    /**
     * @return \nPub\SPI\Persistence\Content\SPIContent
     */
    public function getAssistantObject($assistName)
    {
        if (!isset($this->assistObjects[$assistName])) {
            $assistVal = $this->SPIContent->getPropertyValue($assistName);
            if ($assistVal && (is_array($assistVal) || is_object($assistVal))) {
                $this->assistObjects[$assistName] = SPIContent::ONE_ONE == $this->getMapType($assistName) ? $assistVal : $assistVal[0];
            } else {
                $className = $this->getClassName($assistName);
                if (!class_exists($className)) {
                    throw new Exception(sprintf('Class "%s" not exists', $className));
                }
                $this->assistObjects[$assistName] = new $className();
            }
        }

        return $this->assistObjects[$assistName];
    }

    /**
     * @return string
     */
    public function getClassName($assistName)
    {
        return '\\' . ltrim(trim($this->assistants[$assistName]['class']), '\\');
    }

    /**
     * @return int
     */
    public function getMapType($assistName)
    {
        return (int)$this->assistants[$assistName]['type'];
    }

    /**
     * @return string
     */
    public function getForeignKey($assistName)
    {
        return trim($this->assistants[$assistName]['fkey']);
    }

    /**
     * @return string
     */
    public function getMainKey($assistName)
    {
        return isset($this->assistants[$assistName]['mkey']) ? trim($this->assistants[$assistName]['mkey']) : null;
    }

}

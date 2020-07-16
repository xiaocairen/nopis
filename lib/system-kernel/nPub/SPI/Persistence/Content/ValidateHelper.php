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
use ReflectionClass;
use ReflectionProperty;

/**
 * Description of SPIValidateHelper
 *
 * @author wb
 */
class ValidateHelper
{
    private static $spiContent;
    private static $validator;
    private static $docComments;

    /**
     * @param \nPub\SPI\Persistence\Content\SPIContent $content
     * @return boolean
     */
    public static function validate(SPIContent $content)
    {
        self::$spiContent  = $content;
        self::$validator   = new Validator();
        self::$docComments = [];

        self::parseDocComment();
        self::startValidate();

        return true;
    }

    protected static function startValidate()
    {
        foreach (self::$docComments as $field => $methodParams) {
            $db_type = isset(self::$docComments[$field]['db_type']) ? self::$docComments[$field]['db_type'] : null;
            unset($methodParams['db_type']);
            foreach ($methodParams as $method => $param) {
                self::$validator->$method($field, self::$spiContent->getPropertyValue($field), $param, $db_type);
            }
        }
    }

    protected static function parseDocComment()
    {
        $refl = new ReflectionClass(self::$spiContent);
        foreach ($refl->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED) as $property) {
            $propertyName = $property->getName();
            $docComment = $refl->getProperty($propertyName)->getDocComment();
            if (!$docComment) {
                continue;
            }

            preg_match_all('/\*\s+@([a-z0-9_]+)\s+([^\\n]*)\\n/i', $docComment, $matches);

            foreach ($matches[1] as $key => $method) {
                $method = trim($method);
                $param  = trim($matches[2][$key]);
                if ('var' == $method || 'primary' == $method)
                    continue;

                if ('db_type' != $method && !method_exists(self::$validator, $method)) {
                    if (!IS_DEBUG)
                        continue;

                    throw new Exception(
                        sprintf('The field "%s" docComment method "@%s" not be found in class "%s"', $propertyName, $method, get_class($this->validator)),
                        get_class($this->spiBase)
                    );
                }

                self::$docComments[$propertyName][$method] = $param;
            }
        }
    }
}

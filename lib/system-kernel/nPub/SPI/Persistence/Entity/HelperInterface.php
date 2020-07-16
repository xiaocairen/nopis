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

/**
 *
 * @author wangbin
 */
interface HelperInterface
{
    /**
     * Set field value.
     *
     * @param string $fieldIdentifier
     * @param mixed $value
     */
    public function setField($fieldIdentifier, $value);

    /**
     * Set a list fields value.
     *
     * @param array $fieldValues
     */
    public function setFields(array $fieldValues);

    /**
     * Get entity object.
     *
     * @throws \Exception
     * @return \Nopis\Lib\Entity\ValueObject
     */
    public function getEntity();
}

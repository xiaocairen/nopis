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

use nPub\SPI\Persistence\Entity\Exceptions\ErrorDocValueException;

/**
 * Description of FieldValidator
 *
 * @author wangbin
 */
class FieldValidator
{
    /**
     * access 值 private, protected 和 public 三类。<br />
     *      private 新增时表示禁止设置字段值，更新时表示禁止更新字段值；<br />
     *      protected 新增时表示可以设置字段值，更新时表示只有提供单独api才能更新值的字段；<br />
     *      public 无论新增或更新时都表示可更新字段。<br />
     *
     * @param \nPub\SPI\Persistence\Entity\HelperInterface $helper
     * @param string $param
     * @param string $field
     * @param mixed $value
     * @param boolean $ifset    新增或更新时字段值是否被设置
     */
    public function access(HelperInterface $helper, $param, $field, $value, $ifset)
    {
        if ($helper instanceof CreateHelper) {
            switch ($param) {
                case 'private':
                    if ($ifset) {
                        throw new \Exception(
                            sprintf(
                                'Forbid operation: field \'%s\' of class \'%s\' forbid to be set value in creation',
                                $field,
                                get_class($helper->getEntity())
                            )
                        );
                    }
                    break;

                case 'protected':
                case 'public':
                    break;
                default:
                    throw new ErrorDocValueException($param, 'access', get_class($helper->getEntity()));
            }
        } else {
            switch ($param) {
                case 'private':
                case 'protected':
                    if ($ifset) {
                        throw new \Exception(
                            sprintf(
                                'Forbid operation: field \'%s\' of class \'%s\' forbid to be set value in updation',
                                $field,
                                get_class($helper->getEntity())
                            )
                        );
                    }
                    break;
                case 'public':
                    break;
                default:
                    throw new ErrorDocValueException($param, 'access', get_class($helper->getEntity()));
            }
        }
    }

    /**
     * db_type 表示字段在数据库表中的字段类型及字段长度
     *
     * @param \nPub\SPI\Persistence\Entity\HelperInterface $helper
     * @param string $param
     * @param string $field
     * @param mixed $value
     * @param boolean $ifset    新增或更新时字段值是否被设置
     */
    public function db_type(HelperInterface $helper, $param, $field, $value, $ifset)
    {

    }

    /**
     * primary 值 primary 和 unique。primary 表示自增主键字段；unique 表示唯一字段
     *
     * @param \nPub\SPI\Persistence\Entity\HelperInterface $helper
     * @param string $param
     * @param string $field
     * @param mixed $value
     * @param boolean $ifset    新增或更新时字段值是否被设置
     */
    public function primary(HelperInterface $helper, $param, $field, $value, $ifset)
    {

    }

    /**
     * encrypt 值 password，表示字段为密码字段
     *
     * @param \nPub\SPI\Persistence\Entity\HelperInterface $helper
     * @param string $param
     * @param string $field
     * @param mixed $value
     * @param boolean $ifset    新增或更新时字段值是否被设置
     */
    public function encrypt(HelperInterface $helper, $param, $field, $value, $ifset)
    {

    }
}

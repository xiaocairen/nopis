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

use Nopis\Lib\Pagination\QueryAdapterInterface;

/**
 * SPIContentInterface.
 *
 * @author wb
 */
interface SPIContentInterface
{
    const ONE_ONE  = 1;
    const ONE_MANY = 2;

    /**
     * 返回关系对象列表 <br>
     * 如果有辅助表，返回主表与辅助表的关系 <br>
     * array( <br>
     *   'assist_name_1' => ['type' => 1, 'class' => '\foo\bar\classname', 'fkey' => 'foreign_key1', 'mkey' => 'main_key1'] <br>
     *   'assist_name_2' => ['type' => 2, 'class' => '\foo\bar\classname', 'fkey' => 'foreign_key2', 'mkey' => 'main_key2'] <br>
     * ) <br>
     * type: 1 one to one , 2 one to many
     * class: 副表对应的类
     * fkey: 副表中的关联字段名
     * mkey: 主表中的关联字段名，省略则默认为主表的主键
     *
     * @return array|null
     */
    public function getAssistants();

    /**
     * 返回数据对象对应的数据表全名
     *
     * @return string 数据表名
     */
    public static function getTableName();

    /**
     * 返回主键名
     *
     * @return string 主键
     */
    public static function getPrimaryKey();

    /**
     * 返回主键值
     *
     * @return int
     */
    public function getPrimaryVal();

    /**
     * 设置对象属性
     *
     * @param array $properties     对象属性与属性值组成的关联数组
     */
    public function setProperties(array $properties);

    /**
     * @return int|false
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \nPub\SPI\Persistence\Content\ValidateException
     */
    public function create();

    /**
     * 批量插入多条内容，也支持插入一条内容.
     *
     * @param array $fieldValues    键值对
     * @return int|false            返回插入的数据条数，失败返回false
     */
    public static function createMultiple(array $fieldValues);

    /**
     * @param boolean $updateAssist  if update assist table, default false
     * @param boolean $flush         if call function flush, default false
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \nPub\SPI\Persistence\Content\ValidateException
     */
    public function update(bool $updateAssist = false, bool $flush = false);

    /**
     * 批量更新多条内容
     *
     * @param array $fieldValues    要更新的字段与值组成的数组，eg. [field_1 => value_1, field_2 => value_2, ...]
     * @param string $primaryVal    主键值组成的一维数组
     * @return int|false            返回更新受影响的数据条数，失败返回false
     */
    public static function updateByPrimary(array $fieldValues, string $primaryVal);

    /**
     * 批量更新多条内容
     *
     * @param array $fieldValues    要更新的字段与值组成的数组，eg. [field_1 => value_1, field_2 => value_2, ...]
     * @param array $primaryVals    主键值组成的一维数组
     * @return int|false            返回更新受影响的数据条数，失败返回false
     */
    public static function updateByPrimaryVals(array $fieldValues, array $primaryVals);

    /**
     * 批量更新多条内容
     *
     * @param array $fieldValues    要更新的字段与值组成的数组，eg. [field_1 => value_1, field_2 => value_2, ...]
     * @param string $fieldName     条件字段名
     * @param string $fieldVal      条件字段值
     * @return int|false            返回更新受影响的数据条数，失败返回false
     */
    public static function updateByField(array $fieldValues, string $fieldName, string $fieldVal);

    /**
     * @param boolean $thorough 默认False
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function delete(bool $thorough = false);

    /**
     *
     * @param string $primary_val
     * @param bool $thorough
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function deleteByPrimary(string $primary_val, bool $thorough = false);

    /**
     * 批量删除多条内容
     *
     * @param array $primaryVals    主键值组成的一维数组
     * @return int|false            返回更新受影响的数据条数，失败返回false
     */
    public static function deleteByPrimaryVals(array $primaryVals);

    /**
     * 批量删除多条内容
     *
     * @param string $fieldName     条件字段名
     * @param string $fieldVal      条件字段值
     * @return int|false            返回更新受影响的数据条数，失败返回false
     */
    public static function deleteByField(string $fieldName, string $fieldVal);

    /**
     * 返回一条内容.
     *
     * @param int $contentId
     * @param boolean $loadAssists    默认 false
     * @param boolean $loadDeep       默认 false
     * @return static|false
     */
    public function _load(int $contentId, bool $loadAssists = false, bool $loadDeep = false);

    /**
     * 获取所有内容.
     *
     * @param array|\Nopis\Lib\Database\Params|null $where  数组 eg. [field, =, value] <br>
     *                                                      或者由 _and_ 、 _or_ 、 _in_ 、_not_in_ 、 _between_ 、 _not_between_ <br>
     *                                                      等函数返回的 \Nopis\Lib\Database\Params 对象
     * @param string $sortField                             排序字段
     * @param string $sortType                              排序方式 DESC、 ASC, 默认DESC
     * @param boolean $loadAssist                           是否读取辅助表内容, 默认false不读
     *
     * @return \nPub\SPI\Persistence\Content\SPIContent[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function _loadAll($where, string $sortField = '', string $sortType = 'DESC', bool $loadAssist = false);

    /**
     * 返回内容列表的翻页加载器 \Nopis\Lib\Pagination\Paginator
     *
     * @param int $curPage                                              当前页码
     * @param int $pageSize                                             默认 30
     * @param \Nopis\Lib\Pagination\QueryAdapterInterface $queryAdapter 默认 Null
     * @param boolean $loadAssists                                    默认 False
     * @param boolean $loadDeep                                         默认 False
     *
     * @return \Nopis\Lib\Pagination\Paginator
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function _loadPaginator(int $curPage, int $pageSize = 30, QueryAdapterInterface $queryAdapter = null, bool $loadAssists = false, bool $loadDeep = false);
}

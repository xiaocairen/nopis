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
use Nopis\Lib\Pagination\Query\Criterion;

/**
 * Description of SPIContent
 *
 * @author wb
 */
abstract class SPIContent extends SPIBase implements SPIContentInterface
{

    /**
     * Constructor.
     *
     * @param array $properties
     */
    final public function __construct(array $properties = null)
    {
        if (null === $properties)
            return;

        $propertyHelper = new PropertyHelper($this, $properties);

        parent::__construct($propertyHelper->resovleProperties());
    }

    /**
     * 返回主键值
     *
     * @return int
     */
    final public function getPrimaryVal()
    {
        return $this->getPropertyValue($this->getPrimaryKey());
    }

    /**
     * 设置对象属性
     *
     * @param array $properties     对象属性与属性值组成的关联数组
     */
    final public function setProperties(array $properties)
    {
        if (empty($properties))
            return;

        $propertyHelper = new PropertyHelper($this, $properties);
        $properties = $propertyHelper->resovleProperties();

        foreach ( $properties as $property => $value ) {
            if ($property == $this->getPrimaryKey())
                continue;

            $this->$property = $value;
        }
    }

    /**
     * @return int|false
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     * @throws \nPub\SPI\Persistence\Content\ValidateException
     */
    final public function create()
    {
        return parent::getContentService()->create($this);
    }

    /**
     * 批量插入多条内容，也支持插入一条内容.
     *
     * @param array $fieldValues    键值对
     * @return int|false            返回插入的数据条数，失败返回false
     */
    final public static function createMultiple(array $fieldValues)
    {
        if (!$fieldValues)
            return false;

        return parent::DB()->insert(static::getTableName())
                ->values($fieldValues)->exec();
    }

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
    final public function update(bool $updateAssist = false, bool $flush = false)
    {
        $res = parent::getContentService()->update($this, $updateAssist);
        $res && $flush && $this->flush();
        return $res;
    }
    
    /**
     * 批量更新多条内容
     *
     * @param array $fieldValues    要更新的字段与值组成的数组，eg. [field_1 => value_1, field_2 => value_2, ...]
     * @param string $primaryVal    主键值组成的一维数组
     * @return int|false            返回更新受影响的数据条数，失败返回false
     */
    final public static function updateByPrimary(array $fieldValues, string $primaryVal)
    {
        if (!$fieldValues)
            return false;

        return parent::DB()->update(static::getTableName())
                ->set($fieldValues)
                ->where(static::getPrimaryKey(), '-', $primaryVal)
                ->exec();
    }

    /**
     * 批量更新多条内容
     *
     * @param array $fieldValues    要更新的字段与值组成的数组，eg. [field_1 => value_1, field_2 => value_2, ...]
     * @param array $primaryVals    主键值组成的数组
     * @return int|false            返回更新受影响的数据条数，失败返回false
     */
    final public static function updateByPrimaryVals(array $fieldValues, array $primaryVals)
    {
        if (!$fieldValues || !$primaryVals)
            return false;

        return parent::DB()->update(static::getTableName())
                ->set($fieldValues)
                ->where(_in_(static::getPrimaryKey(), $primaryVals))
                ->exec();
    }

    /**
     * 批量更新多条内容
     *
     * @param array $fieldValues    要更新的字段与值组成的数组，eg. [field_1 => value_1, field_2 => value_2, ...]
     * @param string $fieldName     条件字段名
     * @param string $fieldVal      条件字段值
     * @return int|false            返回更新受影响的数据条数，失败返回false
     */
    final public static function updateByField(array $fieldValues, string $fieldName, string $fieldVal)
    {
        if (!$fieldValues || !$fieldName)
            return false;

        return parent::DB()->update(static::getTableName())
                ->set($fieldValues)
                ->where($fieldName, '=', $fieldVal)
                ->exec();
    }

    /**
     * @param boolean $thorough 默认False
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    final public function delete(bool $thorough = false)
    {
        return parent::getContentService()->delete($this, $thorough);
    }

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
    public function deleteByPrimary(string $primary_val, bool $thorough = false)
    {
        $key = $this->getPrimaryKey();
        $this->$key = $primary_val;
        return parent::getContentService()->delete($this, $thorough);
    }

    /**
     * 批量删除多条内容
     *
     * @param array $primaryVals    主键值组成的一维数组
     * @return int|false            返回更新受影响的数据条数，失败返回false
     */
    final public static function deleteByPrimaryVals(array $primaryVals)
    {
        if (!$primaryVals)
            return false;

        return parent::DB()->delete()->from(static::getTableName())
                ->where(_in_(static::getPrimaryKey(), $primaryVals))
                ->exec();
    }

    /**
     * 批量删除多条内容
     *
     * @param string $fieldName     条件字段名
     * @param string $fieldVal      条件字段值
     * @return int|false            返回更新受影响的数据条数，失败返回false
     */
    final public static function deleteByField(string $fieldName, string $fieldVal)
    {
        return parent::DB()->delete()->from(static::getTableName())
                ->where($fieldName, '=', $fieldVal)
                ->exec();
    }

    /**
     * 返回一条内容.
     *
     * @param int $contentId
     * @param boolean $loadAssists    是否读取辅助表内容, 默认false不读
     * @param boolean $loadDeep       如果辅助表还有下级辅助表是否读取, 默认false不读
     * @return static|false
     */
    final public function _load(int $contentId, bool $loadAssists = false, bool $loadDeep = false)
    {
        $assistants = $this->getAssistants();
        if ($loadAssists && is_array($assistants) && !empty($assistants)) {
            foreach (array_keys($assistants) as $assistName) {
                property_exists($this, $assistName) && $this->$assistName = true;
            }
        }

        return parent::getContentService()->load($this, $contentId, $loadDeep);
    }

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
    final public function _loadAll($where, string $sortField = '', string $sortType = 'DESC', bool $loadAssist = false)
    {
        $assistants = $this->getAssistants();
        if ($assistants) {
            if ($loadAssist) {
                foreach (array_keys($assistants) as $assistName) {
                    property_exists($this, $assistName) && $this->$assistName = true;
                }
            } else {
                foreach (array_keys($assistants) as $assistName) {
                    if (property_exists($this, $assistName) && $this->$assistName === true) {
                        $loadAssist = true;
                        break;
                    }
                }
            }
        }

        return parent::getContentService()->loadAll($this, $where, $sortField, $sortType, $loadAssist);
    }

    /**
     * 返回内容列表的翻页加载器 \Nopis\Lib\Pagination\Paginator
     *
     * @param int $curPage                                              当前页码
     * @param int $pageSize                                             默认 30
     * @param \Nopis\Lib\Pagination\QueryAdapterInterface $queryAdapter 默认 Null
     * @param boolean $loadAssists                                      是否读取辅助表内容, 默认false不读
     * @param boolean $loadDeep                                         如果辅助表还有下级辅助表是否读取, 默认false不读
     *
     * @return \Nopis\Lib\Pagination\Paginator
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    final public function _loadPaginator(int $curPage, int $pageSize = 30, QueryAdapterInterface $queryAdapter = null, bool $loadAssists = false, bool $loadDeep = false)
    {
        $assistants = $this->getAssistants();
        if ($loadAssists && is_array($assistants) && !empty($assistants)) {
            foreach (array_keys($assistants) as $assistName) {
                property_exists($this, $assistName) && $this->$assistName = true;
            }
        }

        if (null === $queryAdapter) {
            $queryAdapter = parent::getQueryAdapter();
            $queryAdapter->from = new Criterion\Table($this->getTableName());
            $queryAdapter->sortClauses = [new Criterion\SortClause($this->getPrimaryKey(), Criterion\SortClause::SORT_DESC)];
        } else {
            !$queryAdapter->from instanceof Criterion\Table && $queryAdapter->from = new Criterion\Table($this->getTableName());
            !$queryAdapter->sortClauses && $queryAdapter->sortClauses = [new Criterion\SortClause($this->getPrimaryKey(), Criterion\SortClause::SORT_DESC)];
        }

        return parent::getContentService()->loadPaginator($this, $queryAdapter, $curPage, $pageSize, $loadDeep);
    }

    public function flush(){ }
}

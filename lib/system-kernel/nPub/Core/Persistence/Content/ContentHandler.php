<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\Persistence\Content;

use Nopis\Lib\Database\DBInterface;
use Nopis\Lib\Pagination\Paginator;
use Nopis\Lib\Pagination\QueryAdapterInterface;
use nPub\SPI\Persistence\Content\ContentHandlerInterface;
use nPub\SPI\Persistence\Content\SPIContent;
use nPub\SPI\Persistence\Content\AssistHelper;

/**
 * Description of Handler
 *
 * @author wangbin
 */
class ContentHandler implements ContentHandlerInterface
{
    /**
     * @var \Nopis\Lib\Database\DBInterface
     */
    private $pdo;

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\Database\DBInterface $pdo
     */
    public function __construct(DBInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create a new content.
     *
     * @param \nPub\SPI\Persistence\Content\SPIContent $content
     *
     * @return int|boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function create(SPIContent $content)
    {
        $assistHelper       = new AssistHelper($content);
        $contentFieldValues = $this->getContentFieldValues($content);

        if ($assistHelper->hasAssistants()) {
            $in = $this->pdo->inTransaction();
            $in || $this->pdo->beginTransaction();

            try {
                if (!$this->pdo->insert($content->getTableName())->values($contentFieldValues)->exec()) {
                    $this->pdo->rollBack();
                    return false;
                }
                $primaryVal = $this->pdo->lastInsertId() ?: $content->getPrimaryVal();

                foreach ($assistHelper->getAssistNames() as $assistName) {
                    $assistObj = $assistHelper->getAssistantObject($assistName);
                    if (!($assistVal = $content->getPropertyValue($assistName)))
                        continue;

                    $mainKey = $assistHelper->getMainKey($assistName);
                    $foreignVal = $mainKey ? $content->getPropertyValue($mainKey) : $primaryVal;
                    switch ($assistHelper->getMapType($assistName)) {
                        case SPIContent::ONE_ONE:
                            $assistFieldValues = $this->getContentFieldValues($assistVal);
                            $assistFieldValues[$assistHelper->getForeignKey($assistName)] = $foreignVal;
                            break;

                        case SPIContent::ONE_MANY:
                            $assistFieldValues = [];
                            foreach ($assistVal as $key => $val) {
                                $assistFieldValues[$key] = $this->getContentFieldValues($val);
                                $assistFieldValues[$key][$assistHelper->getForeignKey($assistName)] = $foreignVal;
                            }
                            break;
                    }

                    if (!$this->pdo->insert($assistObj->getTableName())->values($assistFieldValues)->exec()) {
                        $this->pdo->rollBack();
                        return false;
                    }
                }
                $in || $this->pdo->commit();
            } finally {
                $this->pdo->rollBack();
            }
        } else {
            if (!$this->pdo->insert($content->getTableName())->values($contentFieldValues)->exec()) {
                return false;
            }
            $primaryVal = $this->pdo->lastInsertId() ?: $content->getPrimaryVal();
        }

        return $primaryVal;
    }

    /**
     * Update the given content.
     *
     * @param \nPub\SPI\Persistence\Content\SPIContent $content
     * @param boolean $updateAssist  if update assist table, default false
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function update(SPIContent $content, bool $updateAssist = false)
    {
        $assistHelper       = new AssistHelper($content);
        $primaryKey         = $content->getPrimaryKey();
        $primaryVal         = $content->getPrimaryVal();
        $contentFieldValues = $this->getContentFieldValues($content);

        if ($updateAssist && $assistHelper->hasAssistants()) {
            $in = $this->pdo->inTransaction();
            $in || $this->pdo->beginTransaction();

            try {
                if (false === $this->pdo->update($content->getTableName())->set($contentFieldValues)->where($primaryKey, '=', $primaryVal)->exec()) {
                    $this->pdo->rollBack();
                    return false;
                }

                foreach ($assistHelper->getAssistNames() as $assistName) {
                    if (!($assistant = $content->getPropertyValue($assistName)))
                        continue;

                    switch ($assistHelper->getMapType($assistName)) {
                        case SPIContent::ONE_ONE:
                            $mainKey = $assistHelper->getMainKey($assistName);
                            $foreignVal = $mainKey ? $content->getPropertyValue($mainKey) : $primaryVal;
                            $fieldValues = $this->getContentFieldValues($assistant, false);
                            $fieldValues[$assistHelper->getForeignKey($assistName)] = $foreignVal;
                            $aPrimaryKey = $assistant->getPrimaryKey();
                            $aPrimaryVal = $assistant->getPrimaryVal();
                            if (false === $this->pdo->update($assistant->getTableName())->set($fieldValues)->where($aPrimaryKey, '=', $aPrimaryVal)->exec()) {
                                $this->pdo->rollBack();
                                return false;
                            }
                            break;

                        case SPIContent::ONE_MANY:
                            $fieldValues = [];
                            $assistPriValues = [-1];
                            $mainKey = $assistHelper->getMainKey($assistName);
                            $foreignVal = $mainKey ? $content->getPropertyValue($mainKey) : $primaryVal;
                            foreach ($assistant as $key => $row) {
                                $fieldValues[$key] = $this->getContentFieldValues($row, false);
                                $fieldValues[$key][$assistHelper->getForeignKey($assistName)] = $foreignVal;
                                $row->getPrimaryVal() && $assistPriValues[] =  $row->getPrimaryVal();
                            }
                            $assistObj = current($assistant);

                            $where = _and_([$assistHelper->getForeignKey($assistName), '=', $foreignVal], _not_in_($assistObj->getPrimaryKey(), $assistPriValues));
                            if (false === $this->pdo->delete()->from($assistObj->getTableName())->where($where)->exec()) {
                                $this->pdo->rollBack();
                                return false;
                            }

                            if (false === $this->pdo->insert($assistObj->getTableName())->values($fieldValues)->onDuplicateKeyUpdate(array_keys(current($fieldValues)))->exec()) {
                                $this->pdo->rollBack();
                                return false;
                            }
                            break;
                    }
                }

                $in || $this->pdo->commit();
            } finally {
                $this->pdo->rollBack();
            }

        } elseif (false === $this->pdo->update($content->getTableName())->set($contentFieldValues)->where($primaryKey, '=', $primaryVal)->exec()) {
            return false;
        }

        return true;
    }

    /**
     * Deletes a content.
     *
     * @param \nPub\SPI\Persistence\Content\SPIContent $content
     * @param boolean $thorough
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function delete(SPIContent $content, bool $thorough = false)
    {
        $assistHelper = new AssistHelper($content);
        $primaryKey   = $content->getPrimaryKey();
        $primaryVal   = $content->getPropertyValue($primaryKey);

        if ($thorough || !property_exists($content, 'is_deleted') || $content->getPropertyValue('is_deleted')) {
            if ($assistHelper->hasAssistants()) {
                $in = $this->pdo->inTransaction();
                $in || $this->pdo->beginTransaction();
                try {
                    if (!$this->pdo->delete()->from($content->getTableName())->where($primaryKey, '=', $primaryVal)->exec()) {
                        $this->pdo->rollBack();
                        return false;
                    }

                    if (false === $this->deleteAssists($content, [$primaryVal])) {
                        $this->pdo->rollBack();
                        return false;
                    }

                    $in || $this->pdo->commit();
                } finally {
                    $this->pdo->rollBack();
                }
            } elseif (!$this->pdo->delete()->from($content->getTableName())->where($primaryKey, '=', $primaryVal)->exec()) {
                return false;
            }
        } elseif (!$this->pdo->update($content->getTableName())->set(['is_deleted' => 1])->where($primaryKey, '=', $primaryVal)->exec()) {
            return false;
        }

        return true;
    }

    /**
     * Loads a content by the given content id.
     *
     * @param \nPub\SPI\Persistence\Content\SPIContent $content
     * @param int $contentId
     * @param boolean $loadDeep
     *
     * @return boolean|\nPub\SPI\Persistence\Content\SPIContent
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function load(SPIContent $content, int $contentId, bool $loadDeep = false)
    {
        $assistHelper = new AssistHelper($content);
        $primaryKey   = $content->getPrimaryKey();
        $contentClass = $this->resolveClassName($content);

        $contentObj = $this->pdo->select()->from($content->getTableName())
                ->where($primaryKey, '=', $contentId)
                ->query()->fetch($contentClass);
        if (false === $contentObj)
            return false;

        foreach ($assistHelper->getAssistNames() as $assistName) {
            if (true !== $content->$assistName)
                continue;

            $assistObj  = $assistHelper->getAssistantObject($assistName);
            $mapType    = $assistHelper->getMapType($assistName);
            $foreignKey = $assistHelper->getForeignKey($assistName);
            $mainKey    = $assistHelper->getMainKey($assistName);
            $value      = $mainKey ? $contentObj->$mainKey : $contentId;

            if ($loadDeep && (null != ($_ats = $assistObj->getAssistants()))) {
                foreach (array_keys($_ats) as $_a) {
                    if (property_exists($assistObj, $_a)) {
                        $assistObj->$_a = true;
                    }
                }
            }

            $assistants = $this->loadAll($assistObj, [$foreignKey, '=', $value], '', 'ASC', $loadDeep);
            if (empty($assistants))
                continue;

            $contentObj->$assistName = SPIContent::ONE_ONE == $mapType ? $assistants[0] : $assistants;
        }

        return $contentObj;
    }

    /**
     * Get all contents.
     *
     * @param \nPub\SPI\Persistence\Content\SPIContent $content
     * @param array|\Nopis\Lib\Database\Params|null $where
     * @param string $sortField   sort field
     * @param string $sortType    DESC or ASC
     * @param boolean $loadAssist
     *
     * @return \nPub\SPI\Persistence\Content\SPIContent[]
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadAll(SPIContent $content, $where, string $sortField = '', string $sortType = 'DESC', bool $loadAssist = false)
    {
        $assistHelper = new AssistHelper($content);

        $sortField = $sortField ?: $content->getPrimaryKey();
        $sortType = $sortType ?: 'DESC';
        $order = [$sortField, $sortType];
        if (property_exists($content, 'sort_index')) {
            $order = [['sort_index', 'ASC'], [$sortField, $sortType]];
        }

        $contents = $this->pdo->select()->from($content->getTableName())->where($where)->orderBy($order)
                ->query()->fetchAll($this->resolveClassName($content));

        $assistNames = $assistHelper->getAssistNames();
        if ($loadAssist && $contents && !empty($assistNames)) {
            $primaryIds = [];
            foreach ($contents as $row) {
                $primaryIds[] = $row->getPrimaryVal();
            }

            foreach ($assistNames as $assistName) {
                if (true !== $content->$assistName)
                    continue;

                $aObj = $assistHelper->getAssistantObject($assistName);
                $tMap = $assistHelper->getMapType($assistName);
                $fKey = $assistHelper->getForeignKey($assistName);
                $mKey = $assistHelper->getMainKey($assistName);
                $contentIds = $primaryIds;
                if ($mKey && $mKey != $content->getPrimaryKey()) {
                    $tmp = [];
                    foreach ($contents as $row)
                        $tmp[] = $row->$mKey;
                    $contentIds = $tmp;
                }

                $assistants = $this->loadAll($aObj, _in_($fKey, $contentIds), '', $sortType);
                foreach ($contents as &$row) {
                    $primaryValue = $mKey ? $row->$mKey : $row->getPrimaryVal();
                    $tMap == SPIContent::ONE_MANY && $row->$assistName = [];
                    foreach ($assistants as $assist) {
                        $foreignValue = $assist->getPropertyValue($fKey);
                        if ($primaryValue == $foreignValue) {
                            if ($tMap == SPIContent::ONE_ONE) {
                                $row->$assistName = $assist;
                            } else {
                                array_push($row->$assistName, $assist);
                            }
                        }
                    }
                }
            }
        }

        return $contents;
    }

    /**
     * Get Paginator of contents list.
     *
     * @param \nPub\SPI\Persistence\Content\SPIContent $content
     * @param \Nopis\Lib\Pagination\QueryAdapterInterface $queryAdapter
     * @param int $curPage
     * @param int $pageSize
     * @param boolean $loadDeep
     *
     * @return \Nopis\Lib\Pagination\Paginator
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadPaginator(SPIContent $content, QueryAdapterInterface $queryAdapter, int $curPage = 1, int $pageSize = 20, bool $loadDeep = false)
    {
        $assistHelper = new AssistHelper($content);

        $paginator = new Paginator($queryAdapter, $queryAdapter->selectionIsNull() ? $this->resolveClassName($content) : null);
        $paginator->setPageParams($curPage, $pageSize);

        if (!$paginator->getNbResults() || !count($paginator->getCurrentPageResults()))
            return $paginator;

        $primaryKey = $content->getPrimaryKey();
        $primaryIds = [];
        foreach ($paginator->getCurrentPageResults() as $row) {
            property_exists($row, $primaryKey) && $primaryIds[] = $row->$primaryKey;
        }

        foreach ($assistHelper->getAssistNames() as $assistName) {
            if (true !== $content->$assistName)
                continue;

            $assistObj  = $assistHelper->getAssistantObject($assistName);
            $mapType    = $assistHelper->getMapType($assistName);
            $foreignKey = $assistHelper->getForeignKey($assistName);
            $mainKey    = $assistHelper->getMainKey($assistName);
            $contentIds = $primaryIds;
            if ($mainKey && $mainKey != $primaryKey) {
                $tmp = [];
                foreach ($paginator->getCurrentPageResults() as $row)
                    property_exists($row, $mainKey) && $tmp[] = $row->$mainKey;
                $contentIds = $tmp;
            }

            if ($loadDeep && (null != ($_ats = $assistObj->getAssistants()))) {
                foreach (array_keys($_ats) as $_a) {
                    if (property_exists($assistObj, $_a)) {
                        $assistObj->$_a = true;
                    }
                }
            }

            $assistants = $this->loadAll($assistObj, _in_($foreignKey, $contentIds), $loadDeep);
            if (empty($assistants))
                continue;

            foreach ($paginator->getCurrentPageResults() as &$row) {
                $primaryValue = $mainKey ? $row->$mainKey : $row->$primaryKey;
                $mapType == SPIContent::ONE_MANY && $row->$assistName = [];
                foreach ($assistants as $assist) {
                    $foreignValue = $assist->getPropertyValue($foreignKey);
                    if ($primaryValue == $foreignValue) {
                        if ($mapType == SPIContent::ONE_ONE) {
                            $row->$assistName = $assist;
                        } else {
                            array_push($row->$assistName, $assist);
                        }
                    }
                }
            }
        }

        return $paginator;
    }

    private function deleteAssists(SPIContent $content, array $primaryValues)
    {
        $assistHelper = new AssistHelper($content);

        foreach ($assistHelper->getAssistNames() as $assistName) {
            $mainKey = $assistHelper->getMainKey($assistName);
            if (null !== $mainKey && $mainKey != $content->getPrimaryKey())
                continue;

            $assistObj   = $assistHelper->getAssistantObject($assistName);
            $assistTable = $assistObj->getTableName();
            $where = _in_($assistHelper->getForeignKey($assistName), $primaryValues);

            $assists = $this->pdo->select()->from($assistTable)->where($where)->query()->fetchAll();
            if ($assists) {
                $assistPriValues = [];
                foreach ($assists as $row) {
                    $priKey = $assistObj->getPrimaryKey();
                    $assistPriValues[] = $row->$priKey;
                }

                if (false === $this->pdo->delete()->from($assistTable)->where($where)->exec()) {
                    return false;
                }

                if (false === $this->deleteAssists($assistObj, $assistPriValues)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     *
     * @param SPIContent $content
     * @param boolean $isCreation
     * @return array
     */
    private function getContentFieldValues(SPIContent $content, bool $isCreation = true)
    {
        //ValidateHelper::validate($content);

        $assistants = $content->getAssistants();
        $assistProp = is_array($assistants) && !empty($assistants) ? array_keys($assistants) : [];

        $contentFieldValues = [];
        $refl = new \ReflectionObject($content);
        foreach ($refl->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED) as $property) {
            if ($property->isStatic())
                continue;

            $propertyName = $property->getName();
            if (0 === strpos($propertyName, '__'))
                continue;
            if (($isCreation && $content->getPrimaryKey() == $propertyName && empty($content->getPrimaryVal())) || in_array($propertyName, $assistProp))
                continue;

            $contentFieldValues[$propertyName] = $content->getPropertyValue($propertyName);
        }

        return $contentFieldValues;
    }

    /**
     * @param LatteObject $object
     * @return string
     */
    private function resolveClassName($object)
    {
        return '\\' . trim(get_class($object), '\\');
    }
}

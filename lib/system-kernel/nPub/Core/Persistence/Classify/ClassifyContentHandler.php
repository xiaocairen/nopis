<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\Persistence\Classify;

use Nopis\Lib\Database\DBInterface;
use nPub\SPI\Persistence\Classify\Classify;
use nPub\SPI\Persistence\Classify\ClassifyContentHandlerInterface;
use nPub\SPI\Persistence\Classify\ClassifyContent;
use nPub\SPI\Persistence\Classify\ClassifyContentCreateHelper;

class ClassifyContentHandler implements ClassifyContentHandlerInterface
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
     * Instantiate a Content create helper class
     *
     * @return \nPub\SPI\Persistence\Classify\ClassifyContentCreateHelper
     */
    public function getCreateHelper()
    {
        return new ClassifyContentCreateHelper();
    }

    /**
     * Create a new content.
     *
     * @param \nPub\SPI\Persistence\Classify\ClassifyContentCreateHelper $createHelper
     * @param int $creator
     * @param int $owner
     *
     * @return int|false  return the new content id if create success
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function createClassifyContent(ClassifyContentCreateHelper $createHelper, $creator, $owner)
    {
        $fields = $createHelper->getCreationFieldsValues();
        $fields['creator_uid'] = $creator;
        $fields['owner_uid']   = $owner;

        return $this->pdo->insert(ClassifyContent::tableName())
                ->values($fields)
                ->exec() ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Loads a content by the given classify content id.
     *
     * @param int $contentId
     *
     * @return \nPub\SPI\Persistence\Classify\ClassifyContent|false
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function load($contentId)
    {
        $query = $this->pdo->select()
                ->from(ClassifyContent::tableName())
                ->where(['content_id', '=', $contentId])
                ->query();

        return $query->fetch('\nPub\SPI\Persistence\Classify\ClassifyContent');
    }

    /**
     * Loads a content by the given classifyId, sourceId and tableName.
     *
     * @param int $classifyId
     * @param int $sourceId
     * @param string $tableName
     *
     * @return \nPub\SPI\Persistence\Classify\ClassifyContent|false
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function loadBySource($classifyId, $sourceId, $tableName)
    {
        $query = $this->pdo->select()
                ->from(ClassifyContent::tableName())
                ->where(_and_(
                    ['classify_id', '=', $classifyId],
                    ['source_id', '=', $sourceId],
                    ['main_table', '=', $tableName]
                ))->query();

        return $query->fetch('\nPub\SPI\Persistence\Classify\ClassifyContent');
    }

    /**
     * Delete a content.
     *
     * @param \nPub\SPI\Persistence\Classify\ClassifyContent $classifyContent
     *
     * @return boolean  Return true if delete success, or false when delete failure
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function deleteClassifyContent(ClassifyContent $classifyContent)
    {
        $where = ['content_id', '=', $classifyContent->getContentId()];
        if ($classifyContent->inTrash()) {
            if (!$this->pdo->delete()->from(ClassifyContent::tableName())
                    ->where($where)->exec()) {
                return false;
            }
        } elseif (!$this->pdo->update(ClassifyContent::tableName())->set(['in_trash' => 1])->where($where)->exec()) {
            return false;
        }

        return true;
    }

    /**
     * Delete a list of contents, if $thorough is true, delete thorough.
     *
     * @param array $contentsId
     * @param boolean $thorough
     *
     * @return boolean  Return true if delete success, or false when delete failure
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function deleteClassifyContents(array $contentsId, $thorough = false)
    {
        if ($thorough) {
            if (false === $this->pdo->delete()->from(ClassifyContent::tableName())
                    ->where(_in_('content_id', $contentsId))->exec()) {
                return false;
            }
        } else {
            if (false === $this->pdo->update(ClassifyContent::tableName())->set(['in_trash' => 1])
                    ->where(_in_('content_id', $contentsId))->exec()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Recover a list of contents.
     *
     * @param array $contentsId
     *
     * @return boolean  Return true if delete success, or false when delete failure
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function recoverClassifyContents(array $contentsId)
    {
        if (false === $this->pdo->update(ClassifyContent::tableName())->set(['in_trash' => 0])
                ->where(_in_('content_id', $contentsId))->exec()) {
            return false;
        }

        return true;
    }

    /**
     * Move contents to the given classify.
     *
     * @param array $contentsId
     * @param \nPub\SPI\Persistence\Classify\Classify $classify
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function moveContents2Classify(array $contentsId, Classify $classify)
    {
        if (false === $this->pdo->update(ClassifyContent::tableName())->set(['classify_id' => $classify->getClassifyId()])
                ->where(_in_('content_id', $contentsId))->exec()) {
            return false;
        }

        return true;
    }

    /**
     * Copy contents to the given classify.
     *
     * @param array $contentsId
     * @param \nPub\SPI\Persistence\Classify\Classify $classify
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function copyContents2Classify(array $contentsId, Classify $classify)
    {
        $refl = new \ReflectionClass('\nPub\SPI\Persistence\Classify\ClassifyContent');
        $insertFields = $selectFields = [];
        foreach ($refl->getProperties() as $property) {
            $propertyName = $property->getName();
            if ($propertyName == 'content_id')
                continue;
            $insertFields[] = $propertyName;

            $selectFields[] = $propertyName == 'classify_id' ? $classify->getClassifyId() : $propertyName;
        }

        $this->pdo->prepare('INSERT INTO ' . ClassifyContent::tableName()
                . ' (' . implode(',', $insertFields) . ') '
                . ' SELECT ' . implode(',', $selectFields) . ' FROM ' . ClassifyContent::tableName()
                . ' WHERE content_id IN (' . implode(',', $contentsId) . ') '
                . ' ON DUPLICATE KEY UPDATE in_trash=VALUES(in_trash)');

        return $this->pdo->execute();
    }

    /**
     * Update content.
     *
     * @param int $contentId
     * @param string $title
     * @param string $showAction
     * @param string $thumbs
     * @param string $extras
     * @param int $sortIndex
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function update($contentId, $title = null, $showAction = null, $thumbs = null, $extras = null, $sortIndex = null)
    {
        $set = [];
        if (null !== $title)
            $set['title'] = $title;
        if (null !== $showAction)
            $set['show_action'] = $showAction;
        if (null !== $thumbs)
            $set['thumbs'] = $thumbs;
        if (null !== $extras)
            $set['extras'] = $extras;
        if (null !== $sortIndex)
            $set['sort_index'] = $sortIndex;

        if (!$set)
            return false;

        if (false === $this->pdo->update(ClassifyContent::tableName())->set($set)
                ->where(['content_id', '=', $contentId])->exec()) {
            return false;
        }

        return true;
    }

    /**
     * Update content sort index.
     *
     * @param int $contentId
     * @param int $sortIndex
     *
     * @return boolean
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    public function updateSortIndex($contentId, $sortIndex)
    {
        if (false === $this->pdo->update(ClassifyContent::tableName())->set(['sort_index' => $sortIndex])
                ->where(['content_id', '=', $contentId])->exec()) {
            return false;
        }

        return true;
    }

}
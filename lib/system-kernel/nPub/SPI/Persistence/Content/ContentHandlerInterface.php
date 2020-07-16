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
 * ContentHandlerInterface.
 *
 * @author wangbin
 */
interface ContentHandlerInterface
{
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
    public function create(SPIContent $content);

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
    public function update(SPIContent $content, bool $updateAssist = false);

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
    public function delete(SPIContent $content, bool $thorough = false);

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
    public function load(SPIContent $content, int $contentId, bool $loadDeep = false);

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
    public function loadAll(SPIContent $content, $where, string $sortField = '', string $sortType = 'DESC', bool $loadAssist = false);

    /**
     *
     * Loads Paginator of contents list.
     *
     * @param \nPub\SPI\Persistence\Content\SPIContent $content
     * @param \Nopis\Lib\Pagination\Query\Criterion\FilterInterface $filter
     * @param \Nopis\Lib\Pagination\Query\Criterion\SortClause[] $sortClauses
     * @param int $curPage
     * @param int $pageSize
     *
     * @return \Nopis\Lib\Pagination\Paginator
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     */
    //public function loadPaginator(SPIContent $content, Criterion\FilterInterface $filter = null, array $sortClauses = null, $curPage = 1, $pageSize = 30);

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
    public function loadPaginator(SPIContent $content, QueryAdapterInterface $queryAdapter, int $curPage = 1, int $pageSize = 20, bool $loadDeep = false);
}

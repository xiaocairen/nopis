<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Pagination;

use Nopis\Lib\Pagination\Exceptions\LogicException;
use Nopis\Lib\Pagination\Exceptions\NotIntegerNbPerPageException;
use Nopis\Lib\Pagination\Exceptions\LessThan1NbPerPageException;
use Nopis\Lib\Pagination\Exceptions\NotIntegerCurrentPageException;
use Nopis\Lib\Pagination\Exceptions\LessThan1CurrentPageException;

/**
 * Description of Paginator
 *
 * @author wangbin
 */
class Paginator implements \Countable, \IteratorAggregate, PaginatorInterface
{
    /**
     * @var \Nopis\Lib\Pagination\QueryAdapterInterface
     */
    private $queryAdapter;
    private $currentPage;
    private $nbPerPage;
    private $nbResults;
    private $currentPageResults;

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\Pagination\QueryAdapterInterface $queryAdapter
     * @param string $returnEntity
     */
    public function __construct(QueryAdapterInterface $queryAdapter, $returnEntity = null)
    {
        $this->queryAdapter = $queryAdapter;
        if (null !== $returnEntity)
            $this->queryAdapter->setReturnEntity($returnEntity);
        $this->currentPage = 1;
        $this->nbPerPage = 50;
    }

    /**
     * return query adapter
     *
     * @return \Nopis\Lib\Pagination\QueryAdapterInterface
     */
    public function getQueryAdapter()
    {
        return $this->queryAdapter;
    }

    /**
     * Sets the current page and the number results of per page.
     *
     * Tries to convert from string and float.
     *
     * @param integer $currentPage      the current page number
     * @param integer $nbPerPage        the number results of per page
     *
     * @throws NotIntegerCurrentPageException If the current page is not an integer even converting.
     * @throws LessThan1CurrentPageException  If the current page is less than 1.
     * @throws NotIntegerNbPerPageException   If the number results of per page is not an integer even converting.
     * @throws LessThan1NbPerPageException    If the number results of per page is less than 1.
     */
    public function setPageParams($currentPage, $nbPerPage)
    {
        $this->nbPerPage = $this->filterNbPerPage($nbPerPage);
        $this->currentPage = $this->filterCurrentPage($currentPage);
        $this->resetForPageParamsChange();

        return $this;
    }

    private function filterNbPerPage($nbPerPage)
    {
        $nbPerPage = $this->toInteger($nbPerPage);
        if (!is_int($nbPerPage)) {
            throw new NotIntegerNbPerPageException();
        }

        if ($nbPerPage < 1) {
            throw new LessThan1NbPerPageException();
        }

        return $nbPerPage;
    }

    private function filterCurrentPage($currentPage)
    {
        $currentPage = $this->toInteger($currentPage);
        if (!is_int($currentPage)) {
            throw new NotIntegerCurrentPageException();
        }

        if ($currentPage < 1) {
            throw new LessThan1CurrentPageException();
        }

        // $currentPage > $this->getNbPages() && $currentPage = $this->getNbPages();

        return $currentPage;
    }

    private function resetForPageParamsChange()
    {
        $this->currentPageResults = null;
        $this->nbResults = null;
    }

    /**
     * Returns the total number results of per page.
     *
     * @return integer
     */
    public function getNbPerPage()
    {
        return $this->nbPerPage;
    }

    /**
     * Returns the current page.
     *
     * @return integer
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /*public function setPaginateTemplate()
    {

    }*/

    /**
     * Returns the results for the current page.
     *
     * @return array|\Traversable
     */
    public function getCurrentPageResults()
    {
        if ($this->notCachedCurrentPageResults()) {
            $this->currentPageResults = $this->getCurrentPageResultsFromAdapter();
        }

        return $this->currentPageResults;
    }

    private function notCachedCurrentPageResults()
    {
        return $this->currentPageResults === null;
    }

    private function getCurrentPageResultsFromAdapter()
    {
        $offset = $this->calculateOffsetForCurrentPageResults();
        $length = $this->getNbPerPage();

        return $this->queryAdapter->getSlice($offset, $length);
    }

    private function calculateOffsetForCurrentPageResults()
    {
        return ($this->getCurrentPage() - 1) * $this->getNbPerPage();
    }

    /**
     * Calculates the current page offset start
     *
     * @return int
     */
    public function getCurrentPageOffsetStart()
    {
        return $this->getNbResults() ?
               $this->calculateOffsetForCurrentPageResults() + 1 :
               0;
    }

    /**
     * Calculates the current page offset end
     *
     * @return int
     */
    public function getCurrentPageOffsetEnd()
    {
        return $this->hasNextPage() ?
               $this->getCurrentPage() * $this->getNbPerPage() :
               $this->getNbResults();
    }

    /**
     * Returns the total number of results.
     *
     * @return integer
     */
    public function getNbResults()
    {
        if ($this->notCachedNbResults()) {
            $this->nbResults = $this->queryAdapter->getNbResults();
        }

        return $this->nbResults;
    }

    private function notCachedNbResults()
    {
        return $this->nbResults === null;
    }

    /**
     * Returns the total number of pages.
     *
     * @return integer
     */
    public function getNbPages()
    {
        $nbPages = $this->calculateNbPages();

        if ($nbPages == 0) {
            return $this->minimumNbPages();
        }

        return $nbPages;
    }

    private function calculateNbPages()
    {
        return (int) ceil($this->getNbResults() / $this->getNbPerPage());
    }

    private function minimumNbPages()
    {
        return 1;
    }

    /**
     * Returns if the number of results is higher than the max per page.
     *
     * @return Boolean
     */
    public function haveToPaginate()
    {
        return $this->getNbResults() > $this->nbPerPage;
    }

    /**
     * Returns whether there is next page or not.
     *
     * @return Boolean
     */
    public function hasNextPage()
    {
        return $this->currentPage < $this->getNbPages();
    }

    /**
     * Returns the next page.
     *
     * @return integer
     *
     * @throws LogicException If there is no next page.
     */
    public function getNextPage()
    {
        if (!$this->hasNextPage()) {
            throw new LogicException('There is not next page.');
        }

        return $this->currentPage + 1;
    }

    /**
     * Returns whether there is previous page or not.
     *
     * @return Boolean
     */
    public function hasPrevPage()
    {
        return $this->currentPage > 1;
    }

    /**
     * Returns the previous page.
     *
     * @return integer
     *
     * @throws LogicException If there is no previous page.
     */
    public function getPrevPage()
    {
        if (!$this->hasPrevPage()) {
            throw new LogicException('There is not previous page.');
        }

        return $this->currentPage - 1;
    }

    /**
     * Implements the \Countable interface.
     *
     * Return integer The number of results.
     */
    public function count()
    {
        return $this->getNbResults();
    }

    /**
     * Implements the \IteratorAggregate interface.
     *
     * Returns an \ArrayIterator instance with the current results.
     */
    public function getIterator()
    {
        $results = $this->getCurrentPageResults();

        if ($results instanceof \Iterator) {
            return $results;
        }

        if ($results instanceof \IteratorAggregate) {
            return $results->getIterator();
        }

        return new \ArrayIterator($results);
    }

    private function toInteger($value)
    {
        if ((is_string($value) || is_float($value)) && (int) $value == $value) {
            return (int) $value;
        }

        return $value;
    }

}

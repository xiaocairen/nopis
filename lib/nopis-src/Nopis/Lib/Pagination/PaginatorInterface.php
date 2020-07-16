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

/**
 *
 * @author wangbin
 */
interface PaginatorInterface
{
    /**
     * return query adapter
     *
     * @return \Nopis\Lib\Pagination\QueryAdapterInterface
     */
    // public function getQueryAdapter();

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
    // public function setPageParams($currentPage, $nbPerPage);

    // public function setPaginateTemplate();
}

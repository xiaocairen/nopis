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
interface QueryAdapterInterface
{

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbResults();

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return array|\Traversable The slice.
     */
    public function getSlice($offset, $length);

    /**
     * Set the return entity
     *
     * @param string $entity
     */
    public function setReturnEntity($entity);

    /**
     * Check select if be set.
     *
     * @return boolean
     */
    public function selectionIsNull();
}

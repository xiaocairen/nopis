<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Pagination\Query\Criterion;

/**
 * Description of SortClause
 *
 * @author wangbin
 */
class SortClause
{
    const SORT_ASC = 'ASC';
    const SORT_DESC = 'DESC';

    /**
     * @var string
     */
    protected $target;

    /**
     * @var string
     */
    protected $sortType;

    /**
     * Constructor.
     *
     * @param string $target
     * @param string $sortType
     */
    public function __construct(string $target, string $sortType = self::SORT_ASC)
    {
        if (!$target)
            throw new \InvalidArgumentException('Argument "target" is invalid in class ' . get_class($this));

        $sortType = strtoupper(trim($sortType));
        if ($sortType != self::SORT_ASC && $sortType != self::SORT_DESC) {
            throw new \InvalidArgumentException(
                sprintf('Argument "sortType" expect value is ASC or DESC in class %s. %s given', get_class($this), $sortType)
            );
        }

        $this->target = $target;
        $this->sortType = $sortType;
    }

    /**
     * @return array
     */
    public function getCriterion()
    {
        return [$this->target, $this->sortType];
    }
}

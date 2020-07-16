<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Classify;

use nPub\SPI\Persistence\Entity\UpdateHelper;

/**
 * Description of ClassifyUpdateHelper
 *
 * @author wb
 */
class ClassifyUpdateHelper extends UpdateHelper
{

    /**
     * Constructor.
     *
     * @param \nPub\SPI\Persistence\Classify\Classify $classify
     */
    public function __construct(Classify $classify)
    {
        parent::__construct($classify);
    }

    /**
     * @return \nPub\SPI\Persistence\Classify\Classify
     */
    public function getEntity()
    {
        return $this->entity;
    }
}

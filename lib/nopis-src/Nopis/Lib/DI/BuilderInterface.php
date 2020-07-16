<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\DI;

/**
 * @author wangbin
 */
interface BuilderInterface
{
    /**
     * Make an object by given Class Identifier
     *
     * @param string $classIdentifier
     * @param array  $temporaryInjectionArgs Temporary injection arguments at call-time, nonsupport recursive parameter
     *
     * @return Object
     */
    public function make($classIdentifier, array $temporaryInjectionArgs = []);

    /**
     * Make a singleton object
     *
     * @param string $classIdentifier
     * @param array  $temporaryInjectionArgs Temporary injection arguments at first call-time, nonsupport recursive parameter
     *
     * @return Object
     */
    public function makeSingleton($classIdentifier, array $temporaryInjectionArgs = []);
}

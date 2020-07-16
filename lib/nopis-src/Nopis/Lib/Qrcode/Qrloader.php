<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Qrcode;

include __DIR__ . '/phpqrcode.php';

class Qrloader
{

    /**
     * @return \Nopis\Lib\Qrcode\QRcode
     */
    public static function QRcode()
    {
        return new QRcode();
    }

    /**
     * @return \Nopis\Lib\Qrcode\QRencode
     */
    public static function QRencode()
    {
        return new QRencode();
    }

    /**
     * @return \Nopis\Lib\Qrcode\QRtools
     */
    public static function QRtools()
    {
        return new QRtools();
    }

    /**
     * @param array $source_tab
     * @return \Nopis\Lib\Qrcode\QRarea
     */
    public static function QRarea(array $source_tab)
    {
        return new QRarea($source_tab);
    }

    /**
     * @param int $selfId
     * @param int $sx
     * @param int $sy
     * @return \Nopis\Lib\Qrcode\QRareaGroup
     */
    public static function QRareaGroup($selfId, $sx, $sy)
    {
        return new QRareaGroup($selfId, $sx, $sy);
    }
}
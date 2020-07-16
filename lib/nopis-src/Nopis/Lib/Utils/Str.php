<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Utils;

class Str
{
    /**
     * 可逆加密函数
     *
     * @param string $x   need encode string
     * @param string $key salt key
     * @return string
     */
    public static function encode($x, $key = 'd2JsbXd6eX')
    {
        $n = strlen($x);
        $rs = '';
        $m = md5($key);
        for ($i = $j = 0; $i < $n; $i++) {
            $c = substr($x, $i, 1);
            $k = substr($m, $j, 1);
            $j++;
            if ($j >= 32) {
                $m = md5($m);
                $j = 0;
            }
            $rs .= chr(ord($c) ^ ord($k));
        }
        return base64_encode($rs);
    }

    /**
     * 解密函数，encode() 的逆函数
     *
     * @param string $x   need decode string
     * @param string $key salt key, default base64_encode('wblmwzywzz')
     * @return string
     */
    public static function decode($x, $key = 'd2JsbXd6eX')
    {
        $x = base64_decode($x);
        $n = strlen($x);
        $rs = '';
        $m = md5($key);
        for ($i = $j = 0; $i < $n; $i++) {
            $c = substr($x, $i, 1);
            $k = substr($m, $j, 1);
            $j++;
            if ($j >= 32) {
                $m = md5($m);
                $j = 0;
            }
            $rs .= chr(ord($c) ^ ord($k));
        }
        return $rs;
    }

    /**
     * 反转一个字符串
     *
     * @param string $string
     * @return string
     */
    public static function reverse($string, $code = 'UTF-8')
    {
        $code = strtolower($code);
        $string = (string) $string;
        switch ($code) {
            case 'gb2312':
            case 'gbk':
                $pattern = '/[\xa0-\xff]{2}|[^\xa0-\xff]{1}/i';
                break;
            case 'utf-8':
                $pattern = '/[\xe0-\xef|\x80-\xbf]{3}|[^\xe0-\xef|\x80-\xbf]{1}/i';
                break;
            default:
                return $string;
        }

        $str = '';
        preg_match_all($pattern, $string, $t_string);
        do {
            $str .= array_pop($t_string[0]);
        } while (!empty($t_string[0]));

        return $str;
    }

    /**
     * 剪切一个字符串，中文一个汉字算一个单位长度，英文两个字符算一个单位长度
     *
     * @param string   $string
     * @param int      $cutlen
     * @param string   $code
     * @return string
     */
    public static function cutstr($string, $cutlen, $code = 'UTF-8')
    {
        $code = strtolower($code);
        $string = (string) $string;
        switch ($code) {
            case 'gb2312':
            case 'gbk':
                $pattern = '/[\xa0-\xff]{2}|[^\xa0-\xff]{1,2}/i';
                break;
            case 'utf-8':
                $pattern = '/[\xe0-\xef|\x80-\xbf]{3}|[^\xe0-\xef|\x80-\xbf]{1,2}/i';
                break;
            default:
                return $string;
        }
        preg_match_all($pattern, $string, $t_string);
        return join('', array_slice($t_string[0], 0, $cutlen));
    }

    /**
     * 返回字符串长度，中文一个汉字算一个单位长度，英文两个字符算一个单位长度
     *
     * @param string $string
     * @param string $code
     * @return int
     */
    public static function strlen($string, $code = 'UTF-8')
    {
        $string = (string) $string;
        if (!$string)
            return 0;
        $code = strtolower($code);
        switch ($code) {
            case 'gb2312':
            case 'gbk':
                $pattern = '/[\xa0-\xff]{2}|[^\xa0-\xff]{1,2}/i';
                break;
            case 'utf-8':
                $pattern = '/[\xe0-\xef|\x80-\xbf]{3}|[^\xe0-\xef|\x80-\xbf]{1,2}/i';
                break;
            default:
                return $string;
        }
        preg_match_all($pattern, $string, $t_string);
        return count($t_string[0]);
    }

    /**
     * 将一个字符串转换为十六进制字符串
     *
     * @param string $string
     * @return string
     */
    public static function hex($string)
    {
        $string = (string) $string;
        return '\x' . substr(chunk_split(bin2hex($string), 2, '\x'), 0, -2);
    }

}

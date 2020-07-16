<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nopis\Lib\Security;

/**
 * Description of Encryptor
 *
 * @author wangbin_hn
 */
class Encryptor
{

    /**
     * @var string
     */
    private static $method = 'AES-128-CBC';

    /**
     * @var string
     */
    private static $pass = 'nopis2099_w81';

    public static function encode(string $str) : string
    {
        if (!$str) {
            return '';
        }

        $encrypt = @openssl_encrypt($str, self::$method, self::$pass);
        return $encrypt ? rtrim(strtr($encrypt, '+/', '-_'), '=') : '';
    }

    public static function decode(string $encoded_str) : string
    {
        $len = strlen($encoded_str);
        if ($len == 0) {
            return '';
        }

        $encoded_str = str_pad(strtr($encoded_str, '-_', '+/'), $len % 4, '=', STR_PAD_RIGHT);
        $dec_str = openssl_decrypt($encoded_str, self::$method, self::$pass);

        return $dec_str ?: '';
    }

}

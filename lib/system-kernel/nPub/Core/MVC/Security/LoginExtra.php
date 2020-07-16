<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace nPub\Core\MVC\Security;

/**
 * Description of LoginExtra
 *
 * @author wb
 */
class LoginExtra
{
    /**
     * @var string
     */
    private static $salt = 'ge3A93uv3Qf1';

    /**
     * @var int
     */
    private static $randLength = 3;

    /**
     * @var int
     */
    private static $tsLength = 2;

    /**
     * @var int
     */
    private static $tokenLength = 15;

    /**
     * Generate a token string
     *
     * @return string
     */
    public static function generateExtraToken()
    {
        return self::createToken(self::createRandom(), self::getTimeSign());
    }

    /**
     * Check if the extra token is legal
     *
     * @param string $extraToken
     * @return boolean
     */
    public static function isLegalExtraToken($extraToken)
    {
        if (!$extraToken)
            return false;

        $resource = substr($extraToken, 0, self::$randLength);
        $ts = substr($extraToken, self::$randLength, self::$tsLength);

        return $extraToken === self::createToken($resource, $ts) && $ts === self::getTimeSign();
    }

    /**
     * Create a string
     *
     * @return string
     */
    private static function createRandom()
    {
        $minAscii = 49;
        $maxAscii = 90;
        $noUse = [58, 59, 60, 61, 62, 63, 64, 73, 76, 79, 85];
        $str = '';
        for ($i = 0; $i < self::$randLength;) {
            $randAscii = mt_rand($minAscii, $maxAscii);
            if (!in_array($randAscii, $noUse)) {
                $str .= chr($randAscii);
                $i++;
            }
        }
        return strtolower($str);
    }

    /**
     * Get time sign.
     *
     * @return string
     */
    private static function getTimeSign()
    {
        $h = substr(time(), 5, 1);

        return substr(md5(date('j') . $h), $h, self::$tsLength);
    }

    /**
     * Create a token string
     *
     * @param string $resourceStr
     * @param string $ts
     * @return string
     * @throws SaltNotFoundException
     */
    private static function createToken($resourceStr, $ts)
    {
        $token = md5($resourceStr . self::$salt . $ts);
        return $resourceStr . $ts . substr($token, 0, self::$tokenLength);
    }
}

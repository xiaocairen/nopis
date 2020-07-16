<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\MVC\Security;

use Nopis\Lib\Security\User\UserStoragerInterface;

/**
 * Description of UserStorager
 *
 * @author wangbin
 */
class UserStorager implements UserStoragerInterface
{

    /**
     * @var string
     */
    private $token = 'np_token';

    /**
     * @var string
     */
    private $splitStr = ' && ';

    /**
     * @var string
     */
    private $cryptMethod = 'AES-128-CBC';

    /**
     * @var string
     */
    private $cryptSalt = 'nopis_wlm1012.r6j3';

    /**
     * @var string
     */
    private $cryptIV = 'Jpf3kP7B62tY3h95';

    /**
     * Return a stdClass object with $userCredentials->login and $userCredentials->credentials <br />
     *   or   False when has nobody be storaged in location
     *
     * @return stdClass|false  $userCredentials->login and $userCredentials->credentials
     */
    public function getStoragedUserCredentials()
    {
        if (!isset($_COOKIE[$this->token]) || empty($_COOKIE[$this->token])) {
            return false;
        }
        $token = $_COOKIE[$this->token];
        $token = base64_decode(str_pad(strtr($token, '-_', '+/'), strlen($token) % 4, '=', STR_PAD_RIGHT));

        $_ = openssl_decrypt($token, $this->cryptMethod, $this->cryptSalt, false, $this->cryptIV);
        $_ = explode($this->splitStr, $_);
        if (count($_) != 2)
            return false;

        $userCredentials = new \stdClass;
        $userCredentials->login = $_[0];
        $userCredentials->credentials = $_[1];

        return $userCredentials;
    }

    /**
     * Save the current user's username and password in cookie. <br />
     * $expire = 0 cookie is destroyed on close browser, <br />
     * $expire = -1 Immediately destroy cookie
     *
     * @param string $login
     * @param string $credentials
     * @param int $expire  expire time, Unit of time is second
     *
     * @return boolean
     */
    public function saveCurrentUserCredentials($login, $credentials, $expire = 0)
    {
        $expire = intval($expire);
        if ($expire < 0) {
            if (!isset($_COOKIE[$this->token]) || empty($_COOKIE[$this->token])) {
                return true;
            }
            return setcookie($this->token, '', time() - 1, '/', null, false, true);

        } else {
            $userCredentials = $login . $this->splitStr . $credentials;
            $encrypt = openssl_encrypt($userCredentials, $this->cryptMethod, $this->cryptSalt, false, $this->cryptIV);

            $token = rtrim(strtr(base64_encode($encrypt), '+/', '-_'), '=');
            $expire !== 0 && $expire = time() + $expire;

            return setcookie($this->token, $token, $expire, '/', null, false, true);
        }
    }

    /**
     * Destroy the current user's username and password in cookie. <br />
     * $expire = 0 cookie is destroyed on close browser, <br />
     * $expire = -1 Immediately destroy cookie
     *
     * @return boolean
     */
    public function destroyCurrentUserCredentials()
    {
        return setcookie($this->token, '', time() - 1, '/', null, false, true);
    }
}

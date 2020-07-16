<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Security\User;

/**
 * Description of UsernamePasswordToken
 *
 * @author wangbin
 */
class UserCredentials
{
    /**
     * @var string phone username
     */
    private $login;

    /**
     * @var string password
     */
    private $credentials;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $extra;

    /**
     * Constructor.
     *
     * @param string $login
     * @param string $password
     * @param string $token
     * @param string $extra
     */
    public function __construct(string $login, string $password, string $token, string $extra = '')
    {
        $this->login       = $login;
        $this->credentials = $password;
        $this->token       = $token;
        $this->extra       = $extra;
    }

    /**
     * Check the user login or credentials is legal
     *
     * @return boolean
     */
    public function isLoginRequest()
    {
        return !empty($this->login) && !empty($this->credentials);
    }

    /**
     * Check the user token is not empty
     *
     * @return boolean
     */
    public function hasToken()
    {
        return !empty($this->token);
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getExtra()
    {
        return $this->extra;
    }
}

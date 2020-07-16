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

use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\Security\User\UserEncryptorInterface;
use Nopis\Lib\Security\User\UserInterface;

/**
 * Description of UserEncryptor
 *
 * @author wangbin
 */
class UserEncryptor implements UserEncryptorInterface
{
    /**
     * Encrypt user credentials
     *
     * @param string $credentials
     */
    public function encrypt($credentials)
    {
        return password_hash($credentials, PASSWORD_BCRYPT);
    }

    /**
     * Verify user Credential
     *
     * @param \Nopis\Lib\Security\User\UserInterface $user
     * @param string $credential
     * @return boolean
     */
    public function verifyPassword(UserInterface $user, $credential)
    {
        return password_verify($credential, $user->getPassword());
    }

    /**
     * Generate a user token which is unique
     *
     * @param mixed $userCredentials
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @return string
     */
    public function generateToken($userCredentials, ConfiguratorInterface $configurator)
    {
        if (!$userCredentials)
            return '';

        $openssl = $configurator->getConfig('framework.security.openssl_params');
        $encrypt = openssl_encrypt(json_encode($userCredentials), $openssl['crypt_method'], $openssl['crypt_salt'], false, $openssl['crypt_iv']);

        return rtrim(strtr($encrypt, '+/', '-_'), '=');
    }

    /**
     * Decrypt token
     *
     * @param string $token
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @return boolean|mixed
     */
    public function decryptToken($token, ConfiguratorInterface $configurator)
    {
        if (!$token)
            return false;

        $token = str_pad(strtr($token, '-_', '+/'), strlen($token) % 4, '=', STR_PAD_RIGHT);

        $openssl = $configurator->getConfig('framework.security.openssl_params');
        $_ = openssl_decrypt($token, $openssl['crypt_method'], $openssl['crypt_salt'], false, $openssl['crypt_iv']);

        return json_decode($_);
    }
}

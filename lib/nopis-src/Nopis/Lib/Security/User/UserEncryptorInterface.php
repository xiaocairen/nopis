<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nopis\Lib\Security\User;

use Nopis\Lib\Config\ConfiguratorInterface;

/**
 *
 * @author wangbin
 */
interface UserEncryptorInterface
{
    /**
     * Encrypt user credentials
     *
     * @param string $credentials
     */
    public function encrypt($credentials);

    /**
     * Verify user Credential
     *
     * @param \Nopis\Lib\Security\User\UserInterface $user
     * @param string $credential
     * @return boolean
     */
    public function verifyPassword(UserInterface $user, $credential);

    /**
     * Generate a user token which is unique
     *
     * @param array $userCredentials
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @return string
     */
    public function generateToken($userCredentials, ConfiguratorInterface $configurator);

    /**
     * Decrypt token
     *
     * @param string $token
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     * @return boolean|mixed
     */
    public function decryptToken($token, ConfiguratorInterface $configurator);
}

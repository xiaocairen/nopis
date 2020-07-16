<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Security\Csrf;

use Nopis\Lib\Config\ConfiguratorInterface;

/**
 * Description of CsrfToken
 *
 * @author Wangbin
 */
class CsrfToken
{
    /**
     * @var string
     */
    private $salt = 'Z1P9X6v3aF';

    /**
     * @var int
     */
    private $randLength = 5;

    /**
     * @var int
     */
    private $tsLength = 3;

    /**
     * @var int
     */
    private $tokenLength = 18;

    /**
     * Constructor.
     *
     * @param ConfiguratorInterface $configurator
     */
    public function __construct(ConfiguratorInterface $configurator)
    {
        $salt = $configurator->getConfig('framework.security.salt');
        !empty($salt) && $this->salt = $salt;
    }

    /**
     * Generate a token string
     *
     * @return string
     */
    public function generate()
    {
        return $this->createToken($this->createRandom(), $this->getTimeSign());
    }

    /**
     * Check if the token is legal
     *
     * @param string $token
     * @return boolean
     */
    public function isLegalToken($token)
    {
        if (!$token)
            return false;

        $resource = substr($token, 0, $this->randLength);
        $ts = substr($token, $this->randLength, $this->tsLength);

        return $token === $this->createToken($resource, $ts) && $ts === $this->getTimeSign();
    }

    /**
     * Create a string
     *
     * @return string
     */
    private function createRandom()
    {
        $minAscii = 49;
        $maxAscii = 90;
        $noUse = [58, 59, 60, 61, 62, 63, 64, 73, 76, 79, 85];
        $str = '';
        for ($i = 0; $i < $this->randLength;) {
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
    private function getTimeSign()
    {
        $d = date('j');
        $h = substr(time(), 5, 1);

        return substr(md5($d . $h), $h, $this->tsLength);
    }

    /**
     * Create a token string
     *
     * @param string $resourceStr
     * @param string $ts
     * @return string
     * @throws SaltNotFoundException
     */
    private function createToken($resourceStr, $ts)
    {
        if (empty($this->salt)) {
            throw new SaltNotFoundException('Token salt is empty, you maybe forgot to set it');
        }

        $token = md5($resourceStr . $this->salt . $ts);
        return $resourceStr . $ts . substr($token, 0, $this->tokenLength);
    }
}

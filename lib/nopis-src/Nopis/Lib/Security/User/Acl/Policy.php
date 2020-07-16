<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Security\User\Acl;

use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\Security\User\Role\RoleInterface;

/**
 * Description of Policy
 *
 * @author wangbin
 */
class Policy
{
    /**
     * @var array
     */
    private $platforms;

    /**
     * @var string
     */
    private $forwardLink;

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     */
    public function __construct(ConfiguratorInterface $configurator)
    {
        $this->platforms = $configurator->getConfig('framework.security.platforms');
    }

    /**
     * Check the current visit module if in forbid access module list of user
     *
     * @param \Nopis\Lib\Security\User\Role\RoleInterface $role
     * @param string $mod
     * @return boolean
     * @throws \Exception
     */
    public function inForbidAccessModules(RoleInterface $role, $mod)
    {
        $roleKey = $role->getKey();
        foreach ($this->platforms as $platform) {
            if (in_array($mod, $platform['modules'])) {
                $this->forwardLink = $platform['forward'];
                if (!in_array($roleKey, $platform['rollers'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return forward link if current module is forbid to access
     *
     * @return string
     */
    public function getForwardLink()
    {
        return $this->forwardLink;
    }

    public function getPolicyMods()
    {
        $mods = [];
        foreach ($this->platforms as $platform) {
            $mods += $platform['modules'];
        }
        return $mods;
    }
}

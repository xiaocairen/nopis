<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Security\User\Role;

use Nopis\Lib\Security\User\Acl\Policy;

/**
 * Description of AbstractRole
 *
 * @author wangbin
 */
abstract class AbstractRole implements RoleInterface
{
    /**
     * @var \Nopis\Lib\Security\User\Acl\Policy
     */
    private $policy;

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\Security\User\Acl\Policy $policy
     */
    public function __construct(Policy $policy)
    {
        $this->policy = $policy;
    }

    /**
     * Check the current visit module if in forbid access module list of user
     *
     * @param string $curMod
     * @return boolean
     */
    public function inForbidAccessModules($curMod)
    {
        return $this->policy->inForbidAccessModules($this, $curMod);
    }

    /**
     * Return forward link if current module is forbid to access
     *
     * @return string
     */
    public function getForwardLink()
    {
        return $this->policy->getForwardLink();
    }

    abstract public function getKey();
}

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

use Exception;
use Nopis\Lib\Security\User\UserProviderInterface;
use Nopis\Lib\Security\User\UserCredentials;
use Nopis\Lib\Security\User\NotFoundException as UserNotFoundException;
use Nopis\Lib\Security\SecurityContext;
use nPub\Core\Base\Exceptions\NotFoundException;
use nPub\Core\Base\Exceptions\InvalidPassword;
use nPub\Core\MVC\Controller;

/**
 * UserProvider
 *
 * @author wangbin
 */
class UserProvider implements UserProviderInterface
{

    /**
     * @var \nPub\Core\MVC\Controller
     */
    private $controller;

    /**
     * Constructor.
     *
     * @param \Nopis\Lib\DI\ContainerInterface $container
     * @param \Nopis\Lib\Http\RequestInterface $request
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Load current user, and save the current user in location
     *
     * @param \Nopis\Lib\Security\User\UserCredentials $userCredentials
     *
     * @return \Nopis\Lib\Security\User\UserInterface
     *
     * @throws \Nopis\Lib\Security\User\NotFoundException  with not found code 404 or password invalid code 500
     * @throws \Exception
     */
    public function loadUser(UserCredentials $userCredentials)
    {
        $repository = $this->controller->getRepository();
        try {
            if ($userCredentials->isLoginRequest()) { // login post
                $user = $repository->getUserService()->loadUserByCredentials($userCredentials->getLogin(), $userCredentials->getCredentials());
            } elseif ($userCredentials->hasToken()) {   // for api of app
                $user = $repository->getUserService()->loadUserByToken($userCredentials->getToken());
            } else {
                $user = $repository->getUserService()->loadCurrentUser();
            }
        } catch (NotFoundException $notFound) {
            throw new UserNotFoundException($notFound->getMessage(), SecurityContext::AUTHENTICATION_LOGIN_ERROR);
        } catch (InvalidPassword $invalidPasswd) {
            throw new UserNotFoundException($invalidPasswd->getMessage(), SecurityContext::AUTHENTICATION_PASSWORD_ERROR);
        } catch (Exception $e) {
            throw $e;
        }

        if (!$user) {
            throw new UserNotFoundException('not found user', SecurityContext::AUTHENTICATION_LOGIN_ERROR);
        }

        if (!$user->isAnonymous() && !$userCredentials->hasToken() && false === stripos($this->controller->request->getUrl()->getPath(), 'logout')) {
            $expire = $this->controller->getConfigurator()->getConfig('framework.security.uc_token_cookie_expire');
            $expire = max(0, (int)$expire);
            $repository->getUserService()->localizeCurrentUser($user, $expire);
        }

        return $user;
    }

    /**
     * Loads anonymous user
     *
     * @return \Nopis\Lib\Security\User\UserInterface
     */
    public function loadAnonymousUser()
    {
        return $this->controller->getRepository()->getUserService()->loadAnonymousUser();
    }
}

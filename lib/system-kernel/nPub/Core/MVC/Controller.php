<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Core\MVC;

use Nopis\Lib\Security\AuthenticationFailure;
use Nopis\Framework\Controller\Controller as FrameworkController;
use nPub\Core\Repository\Repository;
use nPub\Core\Persistence\PersistenceHandler;
use nPub\Core\MVC\Security\UserProvider;

/**
 * Description of Controller
 *
 * @author wangbin
 */
abstract class Controller extends FrameworkController
{

    /**
     * @var \nPub\API\Repository\RepositoryInterface
     */
    protected $repository;

    /**
     * @var \nPub\SPI\Persistence\User\User
     */
    private $user;

    /**
     * @return \nPub\API\Repository\RepositoryInterface
     */
    final public function getRepository()
    {
        if (!$this->repository) {
            $this->repository = Repository::getInstance(new PersistenceHandler($this->DB()), $this->getContainer(), $this->getConfigurator());
        }

        return $this->repository;
    }

    /**
     * Get current user, logined or anonymous
     *
     * @return \nPub\SPI\Persistence\User\User
     */
    public function getCurrentUser()
    {
        if (!$this->user) {
            $userProvider = new UserProvider($this);
            try {
                $this->user = $userProvider->loadUser($this->getUserCredentials());
            } catch (\Exception $e) {
                AuthenticationFailure::triggerLoginFailure($e, $this->request);
            } finally {
                if (!$this->user) {
                    $this->user = $userProvider->loadAnonymousUser();
                }
            }
        }

        return $this->user;
    }

    /**
     * @return \nPub\API\Repository\UserServiceInterface
     */
    public function getUserService()
    {
        return $this->getRepository()->getUserService();
    }

    /**
     * @return \nPub\API\Repository\UserGroupServiceInterface
     */
    public function getUserGroupService()
    {
        return $this->getRepository()->getUserGroupService();
    }

    /**
     * 在一个action方法中调用其它模块控制器的action方法
     *
     * @param string $action        参数格式：ModuleName:Controller:action
     * @param array $params         传给其它action的参数
     */
    public function callOtherAction(string $action, array $params = [])
    {
        $this->request->forward($this->getRouter()->generateUrl($action, $params));
    }

    /**
     * Request success, return json string
     *
     * @param mixed  $data      return data
     * @param mixed  $extras    return extras
     * @return \Nopis\Lib\Http\AsyncResponse
     */
    protected function doSuccess($data = null, $extras = null)
    {
        $return['success'] = true;
        null !== $data   && $return['data']   = $data;
        null !== $extras && $return['extras'] = $extras;

        return $this->renderJson($return);
    }

    /**
     * Request failure, return json string
     *
     * @param \Exception $ex
     * @param mixed $extras
     * @return \Nopis\Lib\Http\AsyncResponse
     */
    protected function doFailure(\Exception $ex, $extras = null)
    {
        $return['success']    = false;
        $return['error_code'] = $ex->getCode();
        $return['error_msg']  = $ex->getMessage();
        null !== $extras && $return['extras'] = $extras;

        return $this->renderJson($return);
    }
}

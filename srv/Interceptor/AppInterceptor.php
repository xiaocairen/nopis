<?php

namespace Interceptor;

use Nopis\Framework\Interceptor\InterceptorInterface;
use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Routing\RouterInterface;
use Nopis\Lib\Security\User\Acl\Policy;
use Nopis\Lib\Routing\RouteNotFoundException;
use nPub\Core\MVC\Controller;

/**
 * 全局拦截器
 *
 * @author wb
 */
class AppInterceptor implements InterceptorInterface
{
    public function beforeHandle(RequestInterface $request, RouterInterface $router, Controller $controller)
    {
        $curRouter = $router->getCurRoute();
        $policy = new Policy($controller->getConfigurator());
        if (!in_array($curRouter->getModName(), $policy->getPolicyMods())) {
            return true;
        }

        $user = $controller->getRepository()->getUserService()->loadCurrentUser();
        if ($policy->inForbidAccessModules($user->role(), $curRouter->getModName()) && $curRouter->getCurPath() != ($forwardLink = $policy->getForwardLink())) {
            if (!$forwardLink)
                throw new RouteNotFoundException('Module forbid to access');

            $controller->response->redirect($forwardLink);
            return;
        }
        return true;
    }

    public function afterHandle(RequestInterface $request, \Exception $e = null)
    {
        return null;
    }
}

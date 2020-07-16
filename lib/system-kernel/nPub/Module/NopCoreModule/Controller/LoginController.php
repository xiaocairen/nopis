<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\Module\NopCoreModule\Controller;

use Nopis\Lib\Security\Csrf\CsrfToken;
use Nopis\Lib\Security\SecurityContext;
use nPub\Core\MVC\Security\LoginExtra;

/**
 * Description of DefaultController
 *
 * @author wangbin
 */
class LoginController extends BaseController
{

    public function index()
    {
        if ($this->request->isPost() && $this->getCurrentUser()->isAdmin()) {
            $url_prefix = $this->getRouter()->getCurRoute()->getPrefix();
            $url_prefix = empty($url_prefix) ? '/' : $url_prefix;
            $this->response->redirect($url_prefix);
        }

        switch ($this->request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR_KEY)) {
            case SecurityContext::AUTHENTICATION_LOGIN_ERROR:
                $error = '用户不存在';
                break;

            case SecurityContext::AUTHENTICATION_PASSWORD_ERROR:
                $error = '密码错误';
                break;

            case SecurityContext::AUTHENTICATION_UNKNOWN_ERROR:
                $error = $this->request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR_MSG);
                break;

            default:
                $error = null;
                break;
        }

        if ($error) {
            $this->request->getSession()->remove(SecurityContext::AUTHENTICATION_ERROR_KEY);
        }

        $csrf  = new CsrfToken($this->getConfigurator());

        return $this->render('nPubModuleNopCoreModule:default:login', [
            'csrfToken' => $csrf->generate(),
            'extraToken' => LoginExtra::generateExtraToken(),
            'error' => $error
        ]);
    }

    /**
     * 当用户没有权限访问某个控制器时，需要展现给用户的提示页面
     */
    public function noPermission()
    {
        if (false !== (stripos($this->request->getHeader('Accept'), 'application/json'))) {
            return $this->doFailure(new \Exception('no permission to visit this action'));
        }
        return $this->response->setContent('<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
<title>警告</title>
<style type="text/css">
body{
    text-align: center;
    font-family:"microsoft yahei";
}
p{
    text-align: center;
    width: 80%;
    margin: 68px auto 0;
    font-size: 20px;
}
</style>
</head>
<body>
    <p>抱歉！您没有权限访问此地址...</p>
</body>
</html>');
    }
}
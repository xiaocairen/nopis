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

use Exception;
use Nopis\Lib\Http\Response;
use nPub\Core\Base\Exceptions\InvalidPassword;

/**
 * Description of DefaultController
 *
 * @author wangbin
 */
class DefaultController extends CommonController
{

    /**
     * 首页
     */
    public function index()
    {
        return $this->render('nPubModuleNopCoreModule:default:index');
    }

    /**
     * 退出登陆
     */
    public function logout()
    {
        $this->getRepository()->getUserService()->destroyCurrentUser();
        $forward = $this->getConfigurator()->getConfig('framework.security.platforms.manager.forward');
        $forward = $forward ? $forward : '/login';

        $content = '<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
<title>退出登录</title>
<style type="text/css">
body{
    text-align: center;
    font-family:"microsoft yahei";
}
p{
    text-align: center;
    width: 80%;
    margin: 50px auto 0;
    font-size: 14px;
    font-weight:normal;
}
</style>
</head>
<body>
    <p>已退出登录，页面正在跳转...</p>
    <script type="text/javascript">setTimeout(\'location.href="' . $forward . '"\', 1000);</script>
</body>
</html>';

        return new Response($content);
    }

    /**
     * 修改当前用户的密码
     */
    public function editPassword()
    {
        if (IS_POST) {
            try {
                $oldPwd = $this->request->getPost('old_password');
                $newPwd = $this->request->getPost('new_password');
                $rePwd  = $this->request->getPost('re_password');

                if (!$oldPwd) {
                    throw new \Exception('请输入原密码');
                }
                if (!$newPwd) {
                    throw new \Exception('请输入新密码');
                }
                if (strlen($newPwd) < 6) {
                    throw new \Exception('密码长度不能小于6位');
                }
                if ($newPwd !== $rePwd) {
                    throw new \Exception('两次输入的新密码不一致');
                }

                try {
                    $right = $this->getUserService()->loadUserByCredentials($this->getCurrentUser()->getUsername(), $oldPwd);
                    if (!$right) {
                        throw new \Exception('原密码错误');
                    }
                } catch (InvalidPassword $ip) {
                    throw new \Exception('原密码错误');
                }


                if (!$this->getUserService()->updateUserPassword($this->getCurrentUser(), $newPwd)) {
                    throw new \Exception('更新密码失败');
                }

                return $this->doSuccess();
            } catch (Exception $e) {
                return $this->doFailure($e);
            }
        }

        return $this->render('nPubModuleNopCoreModule:default:edit_password');
    }
}
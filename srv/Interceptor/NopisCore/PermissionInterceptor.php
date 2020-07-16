<?php

namespace Interceptor\NopisCore;

use Exception;
use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Routing\RouterInterface;
use nPub\Core\MVC\Controller;
use Nopis\Framework\Interceptor\InterceptorInterface;

/**
 * 后台权限控制拦截器
 *
 * @author wangbin
 */
class PermissionInterceptor implements InterceptorInterface
{
    public function beforeHandle(RequestInterface $request, RouterInterface $router, Controller $controller)
    {
        if (!$controller->getCurrentUser()->isAnonymous() && !$controller->getCurrentUser()->isAdmin()) {
            return false;
        }
        if ($router->getCurRoute()->getCurPath() == '/') {
            // 如果是后台首页，放行
            return true;
        }
        if ($controller instanceof \nPub\Module\NopCoreModule\Controller\LoginController) {
            // 如果访问的是是后台登陆，直接放行
            return true;
        }
        if ($controller->getCurrentUser()->inSuperGroup()) {
            // 如果是超级管理员，直接放行
            return true;
        }

        // 获取当前用户所在的所有组
        $group_ids = [];
        foreach ($controller->getCurrentUser()->getAdminGroups() as $group) {
            $group_ids[] = $group->getGroupId();
        }
        if (!$group_ids) {
            $request->forward('/noPermission');
            return false;
        }

        // 获取当前用户所在的所有组的权限
        $group_permissions = $controller->getRepository()->getAdminGroupService()->getAllPermissions($group_ids);
        $allow_actions = [];
        foreach ($group_permissions as $p) {
            $allow_actions[] = $p->menu_action;
        }
        if (!$allow_actions) {
            $request->forward('/noPermission');
            return false;
        }

        // 检查权限
        $cur_action = $router->getCurRoute()->getCurAction($request->isPost() ? 'post' : 'get');
        if (in_array($cur_action, $allow_actions)) {
            return true;
        }

        list($mod, $ctl, $act) = explode(':', $cur_action);
        if ('index' != $act) {
            $bm_actions = [];
            foreach ($controller->getRepository()->getBackendMapService()->loadAll() as $map) {
                !empty($map->menu_action) && $bm_actions[] = $map->menu_action;
            }
            /**********************************************************************
             * 这里约定，每个后台controller都应该有一个名为 indexAction 的方法
             *
             * 如果当前 $cur_action 未配置到后台菜单表backend_map中时
             * 可以根据同一个controller中名为 indexAction 的方法，是否在用户的可访问
             * 权限列表中，来决定用户是否有访问此action的权限，
             * 以此可以简化后台菜单表backend_map 和 后台权限控制列表
             * *******************************************************************/
            $cur_action_guider = $mod . ':' . $ctl . ':index';
            if (!in_array($cur_action, $bm_actions) && in_array($cur_action_guider, $allow_actions)) {
                // 如果当前action的引导action在用户的权限列表中，也放行
                return true;
            }
        }

        $request->forward('/noPermission');
        return true;
    }

    public function afterHandle(RequestInterface $request, Exception $e = null)
    {
        return null;
    }
}

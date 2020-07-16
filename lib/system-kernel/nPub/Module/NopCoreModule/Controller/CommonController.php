<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace nPub\Module\NopCoreModule\Controller;

use Nopis\Lib\Routing\RouteRegistryInterface;

/**
 * 做后台菜单的权限检查
 *
 * @author wangbin
 */
class CommonController extends BaseController
{

    /**
     * @var \nPub\SPI\Persistence\Admin\Admin
     */
    protected $currentUser;

    /**
     * @var string
     */
    private $cookie_name = 'np_menu_path';

    /**
     * 根据用户权限，过滤后台可访问菜单列表，
     * 并保存当前访问菜单项到cookie中
     */
    public function __initController()
    {
        if ($this->request->isAjax()) {
            $this->getCurrentUser();
            return null;
        }

        $method = $this->request->isPost() ? RouteRegistryInterface::POST : RouteRegistryInterface::GET;
        $cur_action = $this->getRouter()->getCurRoute()->getCurAction($method);
        if (!$cur_action) {
            $cur_action = $this->getRouter()->getCurRoute()->getCurAction(RouteRegistryInterface::ANY);
        }

        $backend_maps = $this->getRepository()->getBackendMapService()->loadAll();
        $root_path = null;
        foreach ($backend_maps as &$_) {
            $_->map_id = (int) $_->map_id;
            $_->pid = (int) $_->pid;
        }
        foreach ($backend_maps as $map) {
            if ($map->menu_level == 1) {
                continue;
            }
            if ($map->menu_action == $cur_action) {
                $root_path = $map->root_path . '/' . $map->map_id . '/';
                break;
            }
        }

        $np_menu_path = $this->request->getCookie($this->cookie_name);
        if (!$root_path) {
            if (empty($np_menu_path)) {
                throw new \Exception('未找到后台首页菜单');
            } else {
                $root_path = $np_menu_path;
            }
        }
        // 将访问路径保存到cookie中
        $this->setCookie($this->cookie_name, $root_path);

        $bmActionPaths = [];
        foreach ($this->getBackendModActions() as $key => $act) {
            if (isset($act[RouteRegistryInterface::GET])) {
                $bmActionPaths[$act[RouteRegistryInterface::GET]] = $key;
            }
            if (isset($act[RouteRegistryInterface::ANY])) {
                $bmActionPaths[$act[RouteRegistryInterface::ANY]] = $key;
            }
        }
        foreach ($backend_maps as $k => $bm) {
            $backend_maps[$k]->active   = false === strpos($root_path, '/' . $bm->map_id . '/') ? 0 : 1;
            $backend_maps[$k]->menu_url = empty($bm->menu_action) ? '' : $bmActionPaths[$bm->menu_action];
        }

        // 获取当前用户的所有权限
        $permissions = [];
        $is_super = true;
        if (!$this->getCurrentUser()->inSuperGroup()) {
            $is_super = false;
            $group_ids = [];
            foreach ($this->getCurrentUser()->getUserGroups() as $group) {
                if ($group->isForbid())
                    continue;
                $group_ids[] = $group->getGroupId();
            }
            if ($group_ids) {
                $group_permissions = $this->getAdminGroupService()->getAllPermissions($group_ids);
                foreach ($group_permissions as $p) {
                    $permissions[] = $p->getMapId();
                }
            }
        }

        // 根据用户权限，过滤后台菜单
        $backend_maps_filted = [];
        if (!$is_super) {
            foreach ($backend_maps as $map) {
                switch ($map->menu_level) {
                    case 1:
                        $backend_maps_filted[] = $map;
                        break;
                    case 2:
                        if (empty($map->menu_url) || $map->menu_url == '/' || (!empty($map->menu_url) && in_array($map->map_id, $permissions))) {
                            $backend_maps_filted[] = $map;
                        }
                        break;
                    case 3:
                        if (in_array($map->map_id, $permissions) || $map->menu_url == '/') {
                            $backend_maps_filted[] = $map;
                        }
                        break;
                }
            }
        } else {
            $backend_maps_filted = $backend_maps;
        }

        $bm_tree = $this->getRepository()->getBackendMapService()->buildTree($backend_maps_filted);
        // 过滤一二级菜单树
        foreach ($bm_tree as $tk => $top_tree) {
            if (!isset($top_tree->childs) || empty($top_tree->childs)) {
                unset($bm_tree[$tk]);
                continue;
            }
            foreach ($top_tree->childs as $sk => $sub_tree) {
                if (!empty($sub_tree->menu_url))
                    continue;
                if (!isset($sub_tree->childs) || empty($sub_tree->childs)) {
                    unset($bm_tree[$tk]->childs[$sk]);
                }
            }
        }
        // 再次过滤一级菜单树
        foreach ($bm_tree as $tk => $top_tree) {
            if (!isset($top_tree->childs) || empty($top_tree->childs)) {
                unset($bm_tree[$tk]);
            }
        }

        $this->viewShare('backend_maps', $backend_maps);
        $this->viewShare('bm_tree',  $bm_tree);
        $this->viewShare('cur_username', $this->getCurrentUser()->getUsername());

        return null;
    }

    /**
     * @return \nPub\SPI\Persistence\User\User
     */
    public function getCurrentUser()
    {
        if (null === $this->currentUser)
            $this->currentUser = parent::getCurrentUser();

        return $this->currentUser;
    }

    /**
     * 返回后台模块在路由表中配置的所有action控制器
     */
    protected function getBackendModActions()
    {
        $manage_mods = $this->getConfigurator()->getConfig('framework.security.platforms.manager');

        $actions = [];
        $routes = $this->getRouter()->getRouteCollection()->getRoutes();
        foreach ($routes as $route) {
            if (in_array($route->getModName(), $manage_mods['modules'])) {
                $actions = array_merge($actions, $route->getPaths());
            }
        }

        return $actions;
    }

    /**
     * 返回异常处理页面
     */
    protected function renderException(\Exception $e)
    {
        return $this->render('nPubModuleNopCoreModule:default:exception', ['e' => $e]);
    }

}

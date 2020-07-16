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
use Nopis\Lib\Routing\RouteRegistryInterface;

/**
 * Description of BackendMapController
 *
 * @author wangbin
 */
class BackendMapController extends CommonController
{
    /**
     * 后台菜单列表页面
     */
    public function index()
    {
        return $this->render('nPubModuleNopCoreModule:default:backend_map_index');
    }

    /**
     * 添加后台管理菜单
     */
    public function add()
    {
        if (IS_POST) {
            try {
                $pid = $this->request->getPost('pid');
                $menu_name = $this->request->getPost('menu_name');
                $menu_action = $this->request->getPost('menu_action');
                $menu_sort = $this->request->getPost('menu_sort');
                $if_show = $this->request->getPost('if_show');
                if (!$menu_name) {
                    throw new Exception('没有菜单名称');
                }

                $helper = $this->getBackendMapService()->newBackendMapCreateHelper();
                $helper->setFields([
                    'pid' => $pid,
                    'menu_name' => $menu_name,
                    'menu_action' => $menu_action,
                    'menu_sort' => $menu_sort,
                    'if_show' => $if_show,
                ]);

                if (!$this->getBackendMapService()->createBackendMap($helper)) {
                    throw new Exception('保存失败');
                }

                return $this->doSuccess();
            } catch (Exception $e) {
                return $this->doFailure($e);
            }
        }

        try {
            $pid = $this->request->get('pid');
            $menus = $this->getBackendMapService()->loadTree();

            $menu_actions = [];
            foreach ($this->getBackendModActions() as $url => $row) {
                foreach ($row as $m => $a) {
                    if ($m == RouteRegistryInterface::POST)
                        continue;
                    $menu_actions[$url] = $a;
                }
            }

            return $this->render('nPubModuleNopCoreModule:default:backend_map_add', [
                'menu_actions' => $menu_actions,
                'menus' => $menus,
                'pid' => $pid,
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    /**
     * 编辑后台管理菜单
     */
    public function edit()
    {
        if (IS_POST) {
            try {
                $map_id = $this->request->getPost('map_id');
                $pid = $this->request->getPost('pid');
                $menu_name = $this->request->getPost('menu_name');
                $menu_action = $this->request->getPost('menu_action');
                $menu_sort = $this->request->getPost('menu_sort');
                $if_show = $this->request->getPost('if_show');

                if (!$menu_name) {
                    throw new Exception('没有菜单名称');
                }
                $backend_map = $this->getBackendMapService()->load($map_id);
                if (!$backend_map) {
                    throw new Exception('没有找到此菜单');
                }

                $helper = $this->getBackendMapService()->newBackendMapUpdateHelper($backend_map);
                $helper->setFields([
                    'menu_name' => $menu_name,
                    'menu_action' => $menu_action,
                    'menu_sort' => $menu_sort,
                    'if_show' => $if_show,
                ]);

                if ($pid != $backend_map->getPid()) {
                    $parent = $this->getBackendMapService()->load($pid);
                    if (!$parent) {
                        throw new Exception('没有找到所选的父菜单');
                    }
                    if (!$this->getBackendMapService()->updateBackendMap($helper, $parent)) {
                        throw new Exception('保存失败');
                    }
                } else {
                    if (!$this->getBackendMapService()->updateBackendMap($helper)) {
                        throw new Exception('保存失败');
                    }
                }

                return $this->doSuccess();
            } catch (Exception $e) {
                return $this->doFailure($e);
            }
        }

        try {
            $map_id = $this->request->get('map_id');
            $backend_map = $this->getBackendMapService()->load($map_id);
            if (!$backend_map) {
                throw new Exception('没有找到此项菜单');
            }

            $menus = $this->getBackendMapService()->loadTree();

            $menu_actions = [];
            foreach ($this->getBackendModActions() as $url => $row) {
                foreach ($row as $m => $a) {
                    if ($m == RouteRegistryInterface::POST)
                        continue;
                    $menu_actions[$url] = $a;
                }
            }

            return $this->render('nPubModuleNopCoreModule:default:backend_map_edit', [
                'menu_actions' => $menu_actions,
                'menus' => $menus,
                'backend_map' => $backend_map,
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    /**
     * 删除后台管理菜单
     */
    public function del(int $map_id)
    {
        try {
            $backend_map = $this->getBackendMapService()->load($map_id);
            if (!$backend_map) {
                throw new Exception('没有找到记录');
            }

            if (!$this->getBackendMapService()->deleteBackendMap($backend_map)) {
                throw new Exception('删除失败');
            }

            return $this->doSuccess();
        } catch (Exception $ex) {
            return $this->doFailure($ex);
        }
    }

    public function editMenuSort(int $map_id, int $menu_sort)
    {
        try {
            $backend_map = $this->getBackendMapService()->load($map_id);
            if (!$backend_map) {
                throw new Exception('没有找到记录');
            }
            if ($menu_sort < 0 || $menu_sort > 1000) {
                throw new Exception('排序必须大于0且小于1000');
            }

            $helper = $this->getBackendMapService()->newBackendMapUpdateHelper($backend_map);
            $helper->setField('menu_sort', $menu_sort);

            if (!$this->getBackendMapService()->updateBackendMap($helper)) {
                throw new Exception('更新排序失败');
            }

            return $this->doSuccess();
        } catch (Exception $ex) {
            return $this->doFailure($ex);
        }
    }
}

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
use Nopis\Lib\Pagination\Query\QueryAdapter;
use Nopis\Lib\Pagination\Query\Criterion;
use Nopis\Lib\Pagination\Paginator;
use nPub\Core\Base\Exceptions\UnauthorizedException;

/**
 * @author wangbin
 */
class AdminGroupController extends CommonController
{
    /**
     * 管理员组列表首页
     */
    public function index()
    {
        return $this->render('nPubModuleNopCoreModule:default:admin_group_index');
    }

    /**
     * ajax异步获取管理组列表
     */
    public function list(int $offset, int $limit, string $search)
    {
        try {
            $cur_page = $offset ? $offset / $limit + 1 : 1;
            $limit = max(10, $limit);

            $query = new QueryAdapter($this->DB());
            $query->from = new Criterion\Table('admin_group');
            $query->sortClauses = [new Criterion\SortClause('group_id', Criterion\SortClause::SORT_DESC)];
            if ($search) {
                $query->filter = new Criterion\Field('group_name', Criterion\Operator::LIKE, '%' . $search . '%');
            }

            $paginator = new Paginator($query);
            $paginator->setPageParams($cur_page, $limit);

            $total = $paginator->getNbResults();
            $results = $paginator->getCurrentPageResults();

            return $this->doSuccess(['total' => $total, 'rows' => $results]);
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    /**
     * 添加管理员组
     */
    public function add()
    {
        if (IS_POST) {
            try {
                $group_name = $this->request->getPost('group_name');
                $map_ids = $this->request->getPost('map_id', array());
                $is_forbid = $this->request->getPost('is_forbid');

                if (!$group_name) {
                    throw new Exception('没有管理员组名');
                }

                $map_ids = array_unique(array_map('intval', $map_ids));
                foreach ($map_ids as $k => $id) {
                    if ($id <= 0)
                        unset($map_ids[$k]);
                }

                $helper = $this->getAdminGroupService()->newGroupCreateHelper();
                $helper->setField('group_name', $group_name);
                $helper->setField('is_forbid', $is_forbid);

                $this->DB()->beginTransaction();
                $group_id = $this->getAdminGroupService()->createGroup($helper, $this->currentUser);
                if (!$group_id) {
                    $this->DB()->rollBack();
                    throw new Exception('添加管理员组失败');
                }

                if (!empty($map_ids)) {
                    $group = $this->getAdminGroupService()->load($group_id);
                    if (!$this->getAdminGroupService()->setPermissions($group, $map_ids)) {
                        $this->DB()->rollBack();
                        throw new Exception('添加管理员组权限失败');
                    }
                }

                $this->DB()->commit();

                return $this->doSuccess();
            } catch (Exception $e) {
                return $this->doFailure($e);
            }
        }

        try {
            $backend_maps = $this->getBackendMapService()->loadTree();

            return $this->render('nPubModuleNopCoreModule:default:admin_group_add', [
                'backend_maps' => $backend_maps,
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    /**
     * 编辑管理员组
     */
    public function edit()
    {
        if (IS_POST) {
            try {
                $group_id = $this->request->getPost('group_id');
                $group_name = $this->request->getPost('group_name');
                $map_ids = $this->request->getPost('map_id', array());
                $is_forbid = $this->request->getPost('is_forbid');

                if (!$group_name) {
                    throw new Exception('没有管理员组名');
                }

                $map_ids = array_unique(array_map('intval', $map_ids));
                foreach ($map_ids as $k => $id) {
                    if ($id <= 0)
                        unset($map_ids[$k]);
                }

                $group = $this->getAdminGroupService()->load($group_id);
                if (!$group) {
                    throw new Exception('没有找到此管理员组');
                }

                $helper = $this->getAdminGroupService()->newGroupUpdateHelper($group);
                $helper->setField('group_name', $group_name);
                $helper->setField('is_forbid', $is_forbid);

                $this->DB()->beginTransaction();
                if (!$this->getAdminGroupService()->updateGroup($helper, $this->currentUser)) {
                    $this->DB()->rollBack();
                    throw new Exception('添加管理员组失败');
                }

                if (!$this->getAdminGroupService()->setPermissions($group, $map_ids)) {
                    $this->DB()->rollBack();
                    throw new Exception('添加管理员组权限失败');
                }

                $this->DB()->commit();

                return $this->doSuccess();
            } catch (Exception $e) {
                return $this->doFailure($e);
            }
        }

        try {
            $group_id = $this->request->get('group_id');
            $group = $this->getAdminGroupService()->load($group_id);
            if (!$group) {
                throw new Exception('没有找到此管理员组');
            }

            $gmap_ids = [];
            foreach ($group->getBackendMaps() as $bm) {
                $gmap_ids[] = $bm->getMapId();
            }

            return $this->render('nPubModuleNopCoreModule:default:admin_group_edit', [
                'group' => $group,
                'gmap_ids' => $gmap_ids,
                'backend_maps' => $this->getBackendMapService()->loadTree(),
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    /**
     * 删除管理员组
     */
    public function del(int $group_id)
    {
        try {
            $group = $this->getAdminGroupService()->load($group_id);
            if (!$group) {
                throw new Exception('没有此管理员组');
            }

            if (!$this->getAdminGroupService()->deleteGroup($group, $this->currentUser)) {
                throw new Exception('删除管理员组失败');
            }

            return $this->doSuccess();
        } catch (UnauthorizedException $e) {
            return $this->doFailure(new Exception('没有权限删除此管理员组'));
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    /**
     * 管理员组的组员列表
     */
    public function adminList(int $group_id)
    {
        try {
            $group = $this->getAdminGroupService()->load($group_id);
            if (!$group) {
                throw new Exception('管理员组不存在');
            }

            return $this->render('nPubModuleNopCoreModule:default:admin_group_admins', [
                'group_id' => $group->getGroupId(),
                'group_name' => $group->getGroupName()
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    public function _adminList(int $group_id, int $offset, int $limit)
    {
        try {
            $cur_page = $offset ? $offset / $limit + 1 : 1;
            $limit = max(10, $limit);

            $query = new QueryAdapter($this->DB());
            $query->from = new Criterion\Table('admin_group_map', 'g',
                    new Criterion\Join('user', 'a', 'g.user_id = a.user_id'));
            $query->sortClauses = [new Criterion\SortClause('a.user_id', Criterion\SortClause::SORT_DESC)];
            $query->filter = new Criterion\Field('g.group_id', Criterion\Operator::EQ, $group_id);

            $paginator = new Paginator($query);
            $paginator->setPageParams($cur_page, $limit);

            $total = $paginator->getNbResults();
            $results = $paginator->getCurrentPageResults();
            foreach ($results as &$r) {
                $r->reg_time = date('Y-m-d H:i', $r->reg_time);
                $r->last_login_time = $r->last_login_time ? date('Y-m-d H:i', $r->last_login_time) : date('Y-m-d H:i', $r->reg_time);
            }

            return $this->doSuccess(['total' => $total, 'rows' => $results]);
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    public function delFromGroup(int $group_id, int $user_id)
    {
        try {
            if ($user_id == 1 && $group_id == 1) {
                throw new Exception('无操作权限');
            }

            $admin = $this->getUserService()->loadAdmin($user_id);
            if (!$admin) {
                throw new Exception('管理员不存在');
            }
            $group = $this->getAdminGroupService()->load($group_id);
            if (!$group) {
                throw new Exception('管理员组不存在');
            }

            $updateHelper = $this->getUserService()->newUserUpdateHelper($admin);
            $updateHelper->delAdminGroup($group);
            if (!$this->getUserService()->updateAdminGroups($updateHelper)) {
                throw new Exception('移除管理员失败');
            }

            return $this->doSuccess();
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    /**
     * ajax异步获取某个组的权限
     */
    public function getPermissions(int $group_id)
    {
        try {
            $group = $this->getAdminGroupService()->load($group_id);
            if (!$group) {
                throw new Exception('没有此管理员组');
            }

            $bmaps = $this->getAdminGroupService()->getPermissions($group);
            $tree = $this->getBackendMapService()->loadTree();
            $gmap_ids = [];
            foreach ($bmaps as $map) {
                $gmap_ids[] = $map->getMapId();
            }

            return $this->doSuccess(['tree' => $tree, 'gmap_ids' => $gmap_ids]);
        } catch (UnauthorizedException $e) {
            return $this->doFailure(new Exception('没有权限删除此管理员组'));
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    /**
     * 编辑管理员组权限
     */
    public function editPermission(int $group_id, array $map_id)
    {
        try {
            $map_id  = array_unique(array_map('intval', $map_id));
            foreach ($map_id as $k => $id) {
                if ($id <= 0)
                    unset($map_id[$k]);
            }

            if (!$map_id) {
                throw new Exception('没有选择菜单项');
            }
            $group = $this->getAdminGroupService()->load($group_id);
            if (!$group) {
                throw new Exception('没有找到所选的管理员组');
            }
            if (!$this->getAdminGroupService()->setPermissions($group, $map_id)) {
                throw new Exception('更新管理员组权限失败');
            }

            return $this->doSuccess();
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }
}
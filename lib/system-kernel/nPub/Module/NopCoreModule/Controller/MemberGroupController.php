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
use nPub\SPI\Persistence\User\UserGroup;

/**
 * @author wangbin
 */
class MemberGroupController extends CommonController
{
    /**
     * 注册用户组列表首页
     */
    public function index()
    {
        return $this->render('nPubModuleNopCoreModule:default:member_group_index');
    }

    /**
     * ajax异步获取注册用户组列表
     */
    public function list(int $offset, int $limit, string $search)
    {
        try {
            $cur_page = $offset ? $offset / $limit + 1 : 1;
            $limit = max(10, $limit);

            $query = new QueryAdapter($this->DB());
            $query->from = new Criterion\Table(UserGroup::tableName());
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
     * 添加注册用户组
     */
    public function add()
    {
        try {
            if (!IS_POST) {
                throw new Exception('只接受post请求');
            }

            $group_name = $this->request->getPost('group_name');
            $is_forbid  = $this->request->getPost('is_forbid', 0);
            if (!$group_name) {
                throw new Exception('没有用户组名称');
            }

            $helper = $this->getUserGroupService()->newGroupCreateHelper();
            $helper->setField('group_name', $group_name);
            $helper->setField('is_forbid', $is_forbid);

            if (false === ($group_id = $this->getUserGroupService()->createGroup($helper))) {
                throw new Exception('新增用户组失败');
            }

            return $this->doSuccess(['group_id' => $group_id]);
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    /**
     * 编辑注册用户组
     */
    public function edit()
    {
        try {
            if (!IS_POST) {
                throw new Exception('只接受post请求');
            }

            $group_id   = $this->request->getPost('group_id');
            $group_name = $this->request->getPost('group_name');
            $is_forbid  = $this->request->getPost('is_forbid', 0);
            if (!$group_name) {
                throw new Exception('没有用户组名称');
            }

            $group = $this->getUserGroupService()->load($group_id);
            if (!$group) {
                throw new Exception('没有找到此用户组');
            }
            $helper = $this->getUserGroupService()->newGroupUpdateHelper($group);
            $helper->setField('group_name', $group_name);
            $helper->setField('is_forbid', $is_forbid);

            if (!$this->getUserGroupService()->updateGroup($helper)) {
                throw new Exception('更新用户组失败');
            }

            return $this->doSuccess(['group_id' => $group_id]);
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    /**
     * 删除注册用户组
     */
    public function del(int $group_id)
    {
        try {
            $group = $this->getUserGroupService()->load($group_id);
            if (!$group) {
                throw new Exception('没有找到此用户组');
            }

            if (!$this->getUserGroupService()->deleteGroup($group)) {
                throw new Exception('删除用户组失败');
            }

            return $this->doSuccess();
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    public function memberList(int $group_id)
    {
        try {
            $group = $this->getUserGroupService()->load($group_id);
            if (!$group) {
                throw new Exception('用户组不存在');
            }
            if ($this->getUserGroupService()->hasUser($group_id)) {
                throw new Exception('用户组中有用户，无法删除');
            }

            return $this->render('nPubModuleNopCoreModule:default:member_group_members', [
                'group_id' => $group->getGroupId(),
                'group_name' => $group->getGroupName()
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    public function _memberList(int $group_id, int $offset, int $limit)
    {
        try {
            $cur_page = $offset ? $offset / $limit + 1 : 1;
            $limit = max(10, $limit);

            $query = new QueryAdapter($this->DB());
            $query->from = new Criterion\Table('user_group_map', 'g',
                    new Criterion\Join('user', 'u', 'u.user_id = g.user_id'));
            $query->sortClauses = [new Criterion\SortClause('u.user_id', Criterion\SortClause::SORT_DESC)];
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

    public function delFromGroup(int $user_id, int $group_id)
    {
        try {
            $user = $this->getUserService()->loadUser($user_id);
            if (!$user) {
                throw new Exception('用户不存在');
            }
            $group = $this->getUserGroupService()->load($group_id);
            if (!$group) {
                throw new Exception('用户组不存在');
            }

            $updateHelper = $this->getUserService()->newUserUpdateHelper($user);
            $updateHelper->delUserGroup($group);
            if (!$this->getUserService()->updateUserGroups($updateHelper)) {
                throw new Exception('移除用户失败');
            }

            return $this->doSuccess();
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }
}
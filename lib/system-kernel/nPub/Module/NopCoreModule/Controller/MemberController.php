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
use nPub\SPI\Persistence\User\User;

/**
 * @author wangbin
 */
class MemberController extends CommonController
{
    /**
     * 注册用户列表首页
     */
    public function index()
    {
        return $this->render('nPubModuleNopCoreModule:default:member_index');
    }

    /**
     * ajax异步获取注册用户列表
     */
    public function list(int $offset, int $limit, string $search)
    {
        try {
            $cur_page = $offset ? $offset / $limit + 1 : 1;
            $limit = max(10, $limit);

            $query = new QueryAdapter($this->DB());
            $query->from = new Criterion\Table(User::tableName());
            $query->sortClauses = [new Criterion\SortClause('user_id', Criterion\SortClause::SORT_DESC)];
            if ($search) {
                $query->filter = new Criterion\LogicalAnd(
                    new Criterion\Field('is_del', Criterion\Operator::EQ, 0),
                    new Criterion\Field('roler', Criterion\Operator::NOT_EQ, User::ROLER_ADMINISTRATOR),
                    new Criterion\LogicalOr(
                        new Criterion\Field('username', Criterion\Operator::LIKE, '%' . $search . '%'),
                        new Criterion\Field('realname', Criterion\Operator::LIKE, '%' . $search . '%'),
                        new Criterion\Field('phone', Criterion\Operator::LIKE, '%' . $search . '%')
                    )
                );
            } else {
                $query->filter = new Criterion\LogicalAnd(
                    new Criterion\Field('is_del', Criterion\Operator::EQ, 0),
                    new Criterion\Field('roler', Criterion\Operator::NOT_EQ, User::ROLER_ADMINISTRATOR)
                );
            }

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

    /**
     * 添加注册用户
     */
    public function add()
    {
        if (IS_POST) {
            try {
                $username = $this->request->getPost('username');
                $password = $this->request->getPost('password');
                $repassword = $this->request->getPost('repassword');
                $realname = $this->request->getPost('realname');
                $phone = $this->request->getPost('phone');
                $is_forbid = $this->request->getPost('is_forbid');
                $group_id = $this->request->getPost('group_id');

                if (!$username)
                    throw new Exception('请输入用户名');
                if (!$phone)
                    throw new Exception('请输入注册手机号');
                if (mb_strlen($password) < 6)
                    throw new Exception('密码长度小于6位');
                if (!$password || !$repassword)
                    throw new Exception('请输入密码');
                if ($password !== $repassword)
                    throw new Exception('两次输入的密码不一致');

                if ($this->getUserService()->hasLogin($username)) {
                    throw new Exception('用户名已存在');
                }
                if ($this->getUserService()->hasPhone($phone)) {
                    throw new Exception('手机号码已存在');
                }

                $group = null;
                if ($group_id && null === ($group = $this->getUserGroupService()->load($group_id))) {
                    throw new Exception('没有找到所选的用户组');
                }

                $helper = $this->getUserService()->newUserCreateHelper($group?:null);
                $helper->setFields([
                    'username' => $username,
                    'password' => $password,
                    'realname' => $realname,
                    'phone' => $phone,
                    'uuid' => create_uuid($phone),
                    'roler' => User::ROLER_MEMBER,
                    'is_forbid' => $is_forbid,
                ]);

                if (!$this->getUserService()->createUser($helper)) {
                    throw new Exception('创建用户账号失败');
                }

                return $this->doSuccess();
            } catch (Exception $e) {
                return $this->doFailure($e);
            }
        }

        try {
            $groups = $this->getUserGroupService()->loadAll();

            return $this->render('nPubModuleNopCoreModule:default:member_add', [
                'groups' => $groups
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    /**
     * 编辑注册用户
     */
    public function edit()
    {
        if (IS_POST) {
            try {
                $user_id = $this->request->getPost('user_id');
                $password = $this->request->getPost('password');
                $repassword = $this->request->getPost('repassword');
                $realname = $this->request->getPost('realname');
                $is_forbid = $this->request->getPost('is_forbid');
                $new_group_ids = $this->request->getPost('group_id', array());

                if (!empty($password)) {
                    if (mb_strlen($password) < 6)
                        throw new Exception('密码长度小于6位');
                    if (!$password || !$repassword)
                        throw new Exception('请输入密码');
                    if ($password !== $repassword)
                        throw new Exception('两次输入的密码不一致');
                }

                $user = $this->getUserService()->loadUser($user_id);
                if (!$user) {
                    throw new Exception('没有找到此管理员账号');
                }
                $user_group_ids = [];
                foreach ($user->getUserGroups() as $group) {
                    $user_group_ids[] = $group->getGroupId();
                }

                $helper = $this->getUserService()->newUserUpdateHelper($user);

                // 先循环处理新添加的用户组
                $add_group_ids = [];
                foreach ($new_group_ids as $id) {
                    if (!in_array($id, $user_group_ids)) {
                        $add_group_ids[] = $id;
                    }
                }

                // 循环处理需要删除的组
                $del_group_ids = [];
                foreach ($user_group_ids as $id) {
                    if (!in_array($id, $new_group_ids)) {
                        $del_group_ids[] = $id;
                    }
                }

                if ($add_group_ids) {
                    $add_groups = $this->getUserGroupService()->loadAll($add_group_ids);
                    if ($add_groups) {
                        foreach ($add_groups as $g) {
                            $helper->addUserGroup($g);
                        }
                    }
                }
                if ($del_group_ids) {
                    $del_groups = $this->getUserGroupService()->loadAll($del_group_ids);
                    if ($del_groups) {
                        foreach ($del_groups as $g) {
                            $helper->delUserGroup($g);
                        }
                    }
                }

                $this->DB()->beginTransaction();

                $helper->setFields([
                    'realname' => $realname,
                    'is_forbid' => $is_forbid,
                ]);
                if (!$this->getUserService()->updateUser($helper)) {
                    $this->DB()->rollBack();
                    throw new Exception('更新用户基本信息失败');
                }
                if (!empty($password) && !$this->getUserService()->updateUserPassword($user, $password)) {
                    $this->DB()->rollBack();
                    throw new Exception('更新用户密码失败');
                }
                if (!$this->getUserService()->updateUserGroups($helper)) {
                    $this->DB()->rollBack();
                    throw new Exception('更新用户所属组失败');
                }

                $this->DB()->commit();

                return $this->doSuccess();
            } catch (Exception $e) {
                return $this->doFailure($e);
            }
        }

        try {
            $user_id = $this->request->get('user_id');
            $user = $this->getUserService()->loadUser($user_id);
            if (!$user) {
                throw new Exception('没有找到此用户账号');
            }
            $group_ids = [];
            foreach ($user->getUserGroups() as $group) {
                $group_ids[] = $group->getGroupId();
            }

            $groups = $this->getUserGroupService()->loadAll();
            return $this->render('nPubModuleNopCoreModule:default:member_edit', [
                'user' => $user,
                'groups' => $groups,
                'user_group_ids' => $group_ids,
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    /**
     * 删除注册用户
     */
    public function del(int $user_id)
    {
        try {
            $user = $this->getUserService()->loadUser($user_id);
            if (!$user) {
                throw new Exception('没有找到此用户账号');
            }

            if (!$this->getUserService()->deleteUser($user)) {
                throw new Exception('删除用户帐户失败');
            }

            return $this->doSuccess();
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    /**
     * ajax响应jquery validator 检查用户名是否已经存在
     */
    public function checkUsername(string $username)
    {
        try {
            $result = $this->getUserService()->hasLogin($username) ? "false" : "true";

            return $this->response->setContent($result);
        } catch (Exception $e) {
            return $this->response->setContent($e->getMessage());
        }
    }

    /**
     * ajax响应jquery validator 检查手机号是否已经存在
     */
    public function checkPhone(string $phone)
    {
        try {
            $result = $this->getUserService()->hasPhone($phone) ? "false" : "true";

            return $this->response->setContent($result);
        } catch (Exception $e) {
            return $this->response->setContent($e->getMessage());
        }
    }
}
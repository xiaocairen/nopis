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
use nPub\Core\Base\Exceptions\UnauthorizedException;

/**
 * @author wangbin
 */
class AdminController extends CommonController
{
    /**
     * 管理员用户列表首页
     */
    public function index()
    {
        return $this->render('nPubModuleNopCoreModule:default:admin_index');
    }

    /**
     * ajax异步获取管理员列表
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
                    new Criterion\Field('roler', Criterion\Operator::EQ, User::ROLER_ADMINISTRATOR),
                    new Criterion\LogicalOr(
                        new Criterion\Field('username', Criterion\Operator::LIKE, '%' . $search . '%'),
                        new Criterion\Field('realname', Criterion\Operator::LIKE, '%' . $search . '%')
                    )
                );
            } else {
                $query->filter = new Criterion\LogicalAnd(
                    new Criterion\Field('is_del', Criterion\Operator::EQ, 0),
                    new Criterion\Field('roler', Criterion\Operator::EQ, User::ROLER_ADMINISTRATOR)
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
     * 添加管理员用户
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
                $sex = $this->request->getPost('sex');
                $is_forbid = $this->request->getPost('is_forbid');
                $group_id = (int)$this->request->getPost('group_id');

                if (!$username)
                    throw new Exception('请输入用户名');
                if (mb_strlen($password) < 6)
                    throw new Exception('密码长度小于6位');
                if (!$password || !$repassword)
                    throw new Exception('请输入密码');
                if ($password !== $repassword)
                    throw new Exception('两次输入的密码不一致');
                if (!$realname)
                    throw new Exception('请输入真是姓名');

                if (!$phone) {
                    do {
                        $phone = '90' . mt_rand(0, 9) . mt_rand(100, 999) . mt_rand(1000, 9999) . mt_rand(1000, 9999);
                        $has = $this->getUserService()->hasPhone($phone);
                    } while ($has);
                } elseif ($this->getUserService()->hasPhone($phone)) {
                    throw new Exception('手机号已存在');
                }

                if ($this->getUserService()->hasLogin($username)) {
                    throw new Exception('用户名已存在');
                }

                $group = $this->getAdminGroupService()->load($group_id);
                if (!$group) {
                    throw new Exception('没有找到所选的管理员组');
                }

                $helper = $this->getUserService()->newUserCreateHelper(null, $group);
                $helper->setFields([
                    'username' => $username,
                    'phone' => $phone,
                    'password' => $password,
                    'realname' => $realname,
                    'sex' => $sex,
                    'roler' => User::ROLER_ADMINISTRATOR,
                    'is_forbid' => $is_forbid,
                ]);

                if (!$this->getUserService()->createUser($helper)) {
                    throw new Exception('创建管理员账号失败');
                }

                return $this->doSuccess();
            } catch (Exception $e) {
                return $this->doFailure($e);
            }
        }

        try {
            $groups = $this->getAdminGroupService()->loadAll();

            return $this->render('nPubModuleNopCoreModule:default:admin_add', [
                'groups' => $groups
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    /**
     * 编辑管理员用户
     */
    public function edit()
    {
        if (IS_POST) {
            try {
                $user_id = $this->request->getPost('user_id');
                $password = $this->request->getPost('password');
                $repassword = $this->request->getPost('repassword');
                $realname = $this->request->getPost('realname');
                $phone = $this->request->getPost('phone');
                $sex = $this->request->getPost('sex');
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

                $admin = $this->getUserService()->loadAdmin($user_id);
                if (!$admin) {
                    throw new Exception('没有找到此管理员账号');
                }
                $admin_group_ids = [];
                foreach ($admin->getAdminGroups() as $group) {
                    $admin_group_ids[] = $group->getGroupId();
                }

                $helper = $this->getUserService()->newUserUpdateHelper($admin);

                // 先循环处理新添加的用户组
                $add_group_ids = [];
                foreach ($new_group_ids as $id) {
                    if (!in_array($id, $admin_group_ids)) {
                        $add_group_ids[] = $id;
                    }
                }

                // 循环处理需要删除的组
                $del_group_ids = [];
                foreach ($admin_group_ids as $id) {
                    if (!in_array($id, $new_group_ids)) {
                        $del_group_ids[] = $id;
                    }
                }

                if ($add_group_ids) {
                    $add_groups = $this->getAdminGroupService()->loadAll($add_group_ids);
                    if ($add_groups) {
                        foreach ($add_groups as $g) {
                            $helper->addAdminGroup($g);
                        }
                    }
                }
                if ($del_group_ids) {
                    $del_groups = $this->getAdminGroupService()->loadAll($del_group_ids);
                    if ($del_groups) {
                        foreach ($del_groups as $g) {
                            $helper->delAdminGroup($g);
                        }
                    }
                }

                $this->DB()->beginTransaction();

                $helper->setFields([
                    'realname' => $realname,
                    'sex' => $sex,
                    'is_forbid' => $is_forbid,
                ]);
                if (!$this->getUserService()->updateUser($helper)) {
                    $this->DB()->rollBack();
                    throw new Exception('更新用户基本信息失败');
                }
                if ($phone && $admin->getPhone() != $phone && !$this->getUserService()->updateUserPhone($admin, $phone)) {
                    $this->DB()->rollBack();
                    throw new Exception('更新手机号失败');
                }
                if (!empty($password) && !$this->getUserService()->updateUserPassword($admin, $password)) {
                    $this->DB()->rollBack();
                    throw new Exception('更新用户密码失败');
                }
                if (!$this->getUserService()->updateAdminGroups($helper)) {
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
            $admin = $this->getUserService()->loadAdmin($user_id);
            if (!$admin) {
                throw new Exception('没有找到此管理员账号');
            }

            $group_ids = [];
            foreach ($admin->getAdminGroups() as $group) {
                $group_ids[] = $group->getGroupId();
            }

            $groups = $this->getAdminGroupService()->loadAll();

            return $this->render('nPubModuleNopCoreModule:default:admin_edit', [
                'admin' => $admin,
                'groups' => $groups,
                'admin_group_ids' => $group_ids,
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    /**
     * 删除管理员用户
     */
    public function del()
    {
        try {
            $user_id = (int) $this->request->get('user_id');

            $admin = $this->getUserService()->loadAdmin($user_id);
            if (!$admin) {
                throw new Exception('没有此管理员帐户');
            }

            if (!$this->getUserService()->deleteUser($admin)) {
                throw new Exception('删除失败');
            }

            return $this->doSuccess();
        } catch (UnauthorizedException $e) {
            return $this->doFailure(new Exception('没有权限删除此管理员组'));
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    /**
     * ajax响应jquery validator 检查用户名是否已经存在
     */
    public function checkUsername()
    {
        try {
            $username = $this->request->get('username');
            $result = $this->getUserService()->hasLogin($username) ? "false" : "true";

            return $this->response->setContent($result);
        } catch (Exception $e) {
            return $this->response->setContent($e->getMessage());
        }
    }

}
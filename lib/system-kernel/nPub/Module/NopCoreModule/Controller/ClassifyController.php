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

/**
 * @author wangbin
 */
class ClassifyController extends CommonController
{
    /**
     * 文件夹列表首页
     */
    public function index()
    {
        try {
            $classifys = $this->getRepository()->getClassifyService()->loadAll();
            return $this->render('nPubModuleNopCoreModule:default:classify_index', [
                'classifys' => $classifys
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    /**
     * 添加
     */
    public function add()
    {
        if (IS_POST) {
            try {
                $pid = $this->request->getPost('pid', 0);
                $classify_name = $this->request->getPost('classify_name');
                $classify_type = $this->request->getPost('classify_type');
                $description = $this->request->getPost('description');
                $sort_index = $this->request->getPost('sort_index', 100);
                $is_builtin = $this->request->getPost('is_builtin');
                $is_deleted = $this->request->getPost('is_deleted');

                if (!$classify_name) {
                    throw new Exception('文件夹名称不能为空');
                }
                if ($pid && $is_builtin) {
                    throw new Exception('内置文件夹不能有父文件夹');
                }

                $helper = $this->getRepository()->getClassifyService()->newClassifyCreateHelper();
                $helper->setFields([
                    'pid'           => $pid,
                    'classify_name' => $classify_name,
                    'classify_type' => $classify_type,
                    'description'   => $description,
                    'sort_index'    => $sort_index,
                    'is_builtin'    => $is_builtin,
                    'is_deleted'    => $is_deleted,
                ]);

                if (!$this->getRepository()->getClassifyService()->createClassify($helper)) {
                    throw new Exception('新增文件夹失败');
                }

                return $this->doSuccess();
            } catch (Exception $e) {
                return $this->doFailure($e);
            }
        }

        try {
            $classify_tree = $this->getRepository()->getClassifyService()->loadTree();

            return $this->render('nPubModuleNopCoreModule:default:classify_add', [
                'classify_options' => $this->getRepository()->getClassifyService()->buildOptions($classify_tree)
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    /**
     * 编辑
     */
    public function edit()
    {
        if (IS_POST) {
            try {
                $classify_id = $this->request->getPost('classify_id');
                $pid = $this->request->getPost('pid', 0);
                $classify_name = $this->request->getPost('classify_name');
                $classify_type = $this->request->getPost('classify_type');
                $description = $this->request->getPost('description');
                $sort_index = $this->request->getPost('sort_index', 100);
                $is_builtin = $this->request->getPost('is_builtin');
                $is_deleted = $this->request->getPost('is_deleted');

                $classify = $this->getRepository()->getClassifyService()->load($classify_id);
                if (!$classify) {
                    throw new Exception('没有此文件夹');
                }
                if ($classify->isBuiltin()) {
                    throw new Exception('内置文件夹不能编辑');
                }
                if ($classify->isBuiltin() != $is_builtin) {
                    throw new Exception('不能修改文件夹内置属性');
                }

                $parent = null;
                if (0 != $pid && $pid != $classify->getPid()) {
                    $parent = $this->getRepository()->getClassifyService()->load($pid);
                    if (!$parent) {
                        throw new Exception('父文件夹不存在');
                    }
                }

                $helper = $this->getRepository()->getClassifyService()->newClassifyUpdateHelper($classify);
                $helper->setFields([
                    'classify_name' => $classify_name,
                    'classify_type' => $classify_type,
                    'description'   => $description,
                    'sort_index'    => $sort_index,
                    'is_builtin'    => $is_builtin,
                    'is_deleted'    => $is_deleted,
                ]);

                if (!$this->getRepository()->getClassifyService()->updateClassify($helper, $parent)) {
                    throw new Exception('更新文件夹失败');
                }

                return $this->doSuccess([$classify_id]);
            } catch (Exception $e) {
                return $this->doFailure($e);
            }
        }

        try {
            $classify_id = $this->request->get('classify_id');

            $classify = $this->getRepository()->getClassifyService()->load($classify_id);
            if (!$classify) {
                throw new Exception('没有此文件夹');
            }

            $classify_tree = $this->getRepository()->getClassifyService()->loadTree();

            return $this->render('nPubModuleNopCoreModule:default:classify_edit', [
                'classify' => $classify,
                'classify_options' => $this->getRepository()->getClassifyService()->buildOptions($classify_tree, $classify->pid)
            ]);
        } catch (Exception $e) {
            return $this->renderException($e);
        }
    }

    /**
     * 启用
     */
    public function on(int $classify_id)
    {
        try {
            $classify = $this->getRepository()->getClassifyService()->load($classify_id);
            if (!$classify) {
                throw new Exception('没有此文件夹');
            }

            $helper = $this->getRepository()->getClassifyService()->newClassifyUpdateHelper($classify);
            $helper->setField('is_deleted', 0);

            if (!$this->getRepository()->getClassifyService()->updateClassify($helper)) {
                throw new Exception('启用文件夹失败');
            }

            return $this->doSuccess(['classify_id' => $classify_id]);
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    /**
     * 禁用
     */
    public function off(int $classify_id)
    {
        try {
            $classify = $this->getRepository()->getClassifyService()->load($classify_id);
            if (!$classify) {
                throw new Exception('没有此文件夹');
            }
            if ($classify->isBuiltin()) {
                throw new Exception('内置文件夹不能编辑');
            }


            if (!$this->getRepository()->getClassifyService()->deleteClassify($classify)) {
                throw new Exception('禁用文件夹失败');
            }

            return $this->doSuccess(['classify_id' => $classify_id]);
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }

    public function editSortIndex(int $classify_id, int $value)
    {
        try {
            if ($value < 0)
                throw new Exception('值必须大于零');

            $classify = $this->getRepository()->getClassifyService()->load($classify_id);
            if (!$classify) {
                throw new Exception('没有此文件夹');
            }

            $helper = $this->getRepository()->getClassifyService()->newClassifyUpdateHelper($classify);
            $helper->setFields(['sort_index' => $value]);

            if (!$this->getRepository()->getClassifyService()->updateClassify($helper)) {
                throw new Exception('设置文件夹排序失败');
            }

            return $this->doSuccess();
        } catch (Exception $e) {
            return $this->doFailure($e);
        }
    }
}
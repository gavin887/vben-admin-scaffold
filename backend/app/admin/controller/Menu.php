<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\response\Json;
use app\admin\model\admin\Menu as MenuModel;
/**
 * 系统菜单
 * Class Menu
 * @package app\admin\controller
 */
class Menu extends Controller
{
    /**
     * 列表
     * @return Json
     */
    public function lists(): Json
    {
        $model = new MenuModel;
        $list = $model->getList($this->request->param(), $this->admin['role_id']);
        return $this->renderSuccess(['items'=>$list]);
    }
    /**
     * 树
     * @return Json
     */
    public function trees(): Json
    {
        $model = new MenuModel;
        $list = $model->getList($this->request->param(), $this->admin['role_id']);
        return $this->renderSuccess($model->getTreeData($list));
    }
    /**
     * 设置状态
     * @return Json
     */
    public function setstatus(): Json
    {
        $params = $this->postData();
        $model = MenuModel::detail($params['id']);
        // 更新记录
        if ($model->save(['status' => $params['status']])) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 添加
     * @return Json
     */
    public function add(): Json
    {
        $model = new MenuModel;
        if ($model->add($this->postForm())) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑
     * @param int $id
     * @return Json
     */
    public function edit(int $id): Json
    {
        $model = MenuModel::detail($id);
        if ($model->edit($this->postForm())) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除
     * @param int $id
     * @return Json
     */
    public function delete(int $id): Json
    {
        $model = MenuModel::detail($id);
        if (!$model->delete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
}

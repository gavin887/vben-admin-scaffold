<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\response\Json;
use app\admin\model\admin\Dept as DeptModel;
/**
 * 后台部门
 * Class Dept
 * @package app\admin\controller
 */
class Dept extends Controller
{
    /**
     * 列表
     * @return Json
     */
    public function lists(): Json
    {
        $model = new DeptModel;
        $list = $model->getList($this->request->param());
        return $this->renderSuccess(['items'=>$list]);
    }
    /**
     * 树
     * @return Json
     */
    public function trees(): Json
    {
        $model = new DeptModel;
        $list = $model->getList(['status'=>1])->toArray();
        $trees = $model->getTreeData($list);
        return $this->renderSuccess($trees);
    }
    /**
     * 设置状态
     * @return Json
     */
    public function setstatus(): Json
    {
        $params = $this->postData();
        $model = DeptModel::detail($params['id']);
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
        $model = new DeptModel;
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
        $model = DeptModel::detail($id);
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
        $model = DeptModel::detail($id);
        if (!$model->delete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
}

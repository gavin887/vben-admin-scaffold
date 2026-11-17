<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\response\Json;
use app\admin\model\admin\Admin as AdminModel;
use app\admin\model\admin\Dept as DeptModel;
use app\admin\model\admin\Role as RoleModel;
/**
 * 后台账号
 * Class Admin
 * @package app\admin\controller
 */
class Admin extends Controller
{
    /**
     * 列表
     * @return Json
     */
    public function lists(): Json
    {
        $model = new AdminModel;
        $list = $model->getList($this->request->param())->toArray();
        return $this->renderSuccess([
            'total' => $list['total'],
            'items' => $list['data']
        ]);
    }
    /**
     * 详情
     * @return Json
     */
    public function detail(): Json
    {
        $data = AdminModel::detail($this->admin['admin_user_id']);
        return $this->renderSuccess($data->toArray());
    }
    /**
     * 设置状态
     * @return Json
     */
    public function setstatus(): Json
    {
        $params = $this->postData();
        $this->protectAccount((int)$params['id']);
        $model = AdminModel::detail($params['id']);
        if ($model->edit(['status' => $params['status']])) {
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
        $data = AdminModel::sanitize($this->postForm() ?? [], true);
        $this->protectAccount(0, $data);
        $this->validateRole($data);
        $model = new AdminModel;
        if ($model->add($data)) {
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
        $data = AdminModel::sanitize($this->postForm() ?? []);
        $this->protectAccount($id, $data);
        $this->validateRole($data);
        $model = AdminModel::detail($id);
        if ($model->edit($data)) {
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
        $this->protectAccount($id);
        if ($id === (int)$this->admin['admin_user_id']) throw new \think\Exception('不能删除当前登录账号',422);
        $model = AdminModel::detail($id);
        if (!$model->delete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    private function validateRole(array $data): void
    {
        if (array_key_exists('role_id', $data) && !\think\facade\Db::name('admin_role')->where(['admin_role_id'=>$data['role_id'],'status'=>1])->find()) {
            throw new \think\Exception('请选择有效角色',422);
        }
    }

    private function protectAccount(int $id, array $data = []): void
    {
        $superId = 10001;
        if ((int)$this->admin['role_id'] === $superId) return;
        $targetRole = $id ? (int)\think\facade\Db::name('admin_user')->where('admin_user_id',$id)->value('role_id') : 0;
        if ($targetRole === $superId || (int)($data['role_id'] ?? 0) === $superId) {
            throw new \think\Exception('仅超级管理员可管理超级管理员账号',403);
        }
    }
}

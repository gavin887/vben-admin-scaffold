<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\response\Json;
use app\admin\model\admin\Role as RoleModel;
use app\admin\model\admin\RoleMenu as RoleMenuModel;
/**
 * 后台角色管理
 * Class Role
 * @package app\admin\controller
 */
class Role extends Controller
{
    /**
     * 列表
     * @return Json
     */
    public function lists(): Json
    {
        $model = new RoleModel;
        $list = $model->getList($this->request->param())->toArray();
        return $this->renderSuccess([
            'total' => $list['total'],
            'items' => $list['data']
        ]);
    }
    /**
     * 全部记录
     * @return Json
     */
    public function all(): Json
    {
        $model = new RoleModel;
        $list = $model->getAll()->toArray();
        return $this->renderSuccess($list);
    }
    /**
     * 修改状态
     * @return Json
     */
    public function setstatus(): Json
    {
        $params = $this->postData();
        $this->protectRole((int)($params['id'] ?? 0), $params);
        $model = RoleModel::detail($params['id']);
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
        $model = new RoleModel;
        $data = $this->postForm();
        $this->protectRole(0, $data);
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
        $data = $this->postForm();
        $this->protectRole($id, $data);
        $model = RoleModel::detail($id);
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
        if ($id === 10001) throw new \think\Exception('不能删除初始化管理员角色',403);
        if (\think\facade\Db::name('admin_user')->where('role_id',$id)->count()) throw new \think\Exception('角色仍被账号使用',422);
        \think\facade\Db::transaction(function() use ($id) {
            RoleMenuModel::where('role_id',$id)->delete();
            RoleModel::destroy($id);
        });
        return $this->renderSuccess('删除成功');
    }

    private function protectRole(int $id, array $data): void
    {
        if (($data['value'] ?? '') === 'supperadmin' && $id !== 10001) throw new \think\Exception('超级管理员角色标识为保留值',403);
        if ($id === 10001) {
            if ((int)$this->admin['role_id'] !== 10001) throw new \think\Exception('仅超级管理员可修改此角色',403);
            if ((isset($data['value']) && $data['value'] !== 'supperadmin') || (isset($data['status']) && (int)$data['status'] !== 1)) {
                throw new \think\Exception('初始化管理员角色不能禁用或更改标识',422);
            }
        }
    }
}

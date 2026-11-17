<?php
declare (strict_types=1);

namespace app\admin\model\admin;

use app\common\model\admin\Role as RoleModel;
use app\admin\model\admin\RoleMenu as RoleMenuModel;

/**
 * 后台角色模型
 * Class Role
 * @package app\admin\model\admin
 */
class Role extends RoleModel
{
    public static function writable(array $data): array
    {
        return array_intersect_key($data, array_flip(['name','value','sort','status','remark']));
    }
    /**
     * 获取列表
     * @param array $param
     * @return \think\Paginator
     */
    public function getList(array $param = []): \think\Paginator
    {
        // 查询参数
        $params = $this->setQueryDefaultValue($param, [
            'name' => '',    // 名称
            'status' => -1,  // 状态
        ]);
        // 检索查询条件
        $filter = [];
        // 名称
        !empty($params['name']) && $filter[] = ['name', 'like', "%{$params['name']}%"];
        // 状态
        $params['status'] > -1 && $filter[] = ['status', '=', $params['status']];
        // 查询列表数据
        return $this->with(['menu'])
            ->where($filter)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(input('pageSize',20,'intval'))->each(function($item, $key){
                $item['menu'] = array_column($item['menu']->toArray(), 'menu_id');
                return $item;
            });
    }

    public function getAll(): \think\Collection
    {
        // 查询列表数据
        return $this->where([['status', '=', 1]])
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
    }

    /**
     * 添加新记录
     * @param $data
     * @return bool
     */
    public function add($data): bool
    {
        $this->transaction(function () use ($data) {
            // 新增角色记录
            $record = self::writable($data);
            $this->save($record);
            // 新增角色菜单关系记录
            RoleMenuModel::increased((int)$this['admin_role_id'], $data['menu'] ?? []);
        });
        return true;
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool
     */
    public function edit($data): bool
    {
        $this->transaction(function () use ($data) {
            // 更新角色记录
            $record = self::writable($data);
            $this->save($record);
            // 更新角色菜单关系记录
            if(isset($data['menu'])){
                RoleMenuModel::updates((int)$this['admin_role_id'], $data['menu'] ?? []);
            }
        });
        return true;
    }
}

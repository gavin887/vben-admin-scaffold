<?php
declare (strict_types=1);

namespace app\common\model\admin;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 后台菜单模型
 * Class Menu
 * @package app\common\model\admin
 */
class Menu extends BaseModel
{
    // 定义表名
    protected $name = 'admin_menu';

    // 定义主键
    protected $pk = 'admin_menu_id';
    protected $json = ['meta'];
    protected $jsonAssoc = true;

    /**
     * 菜单详情
     * @param int|array $where
     * @param array $with
     * @return static|array|null
     */
    public static function detail($where, array $with = [])
    {
        return static::find($where, $with);
    }

    /**
     * 获取列表记录
     * @param array $param
     * @return array
     */
    public function getList(array $param = [], int $role_id=0): \think\Collection
    {
        $list = $this->getAll($param, $role_id);
        return $list;
    }

    /**
     * 获取所有记录
     * @param array $param
     * @return \think\Collection
     */
    protected function getAll(array $param = [], int $role_id=0): \think\Collection
    {
        // 默认查询参数
        $params = $this->setQueryDefaultValue($param, [
            'name' => '',  // 名称
            'status' => 1  // 状态(1显示 0隐藏 -1全部)
        ]);
        // 设置检索条件
        $filter = [];
        $params['status'] > -1 && $filter[] = ['status', '=', $params['status']];
        !empty($params['name']) && $filter[] = ['name', 'like', "%{$params['name']}%"];
        // 查询列表数据
        if(Db::name('admin_role')->where('admin_role_id', $role_id)->value('value') == 'supperadmin'){
            return $this->where($filter)
                ->order(['sort', 'create_time'])
                ->select();
        }else{
            $ids = array_map('intval', Db::name('admin_role_menu')->where('role_id',$role_id)->column('menu_id'));
            $ids = array_values(array_unique(array_merge($ids,[10001,10009])));
            $parents = $this->column('pid','admin_menu_id');
            foreach ($ids as $id) {
                $seen = [];
                while (!empty($parents[$id]) && !isset($seen[$id])) {
                    $seen[$id] = true;
                    $id = (int)$parents[$id];
                    $ids[] = $id;
                }
            }
            return $this->where($filter)->whereIn('admin_menu_id',array_unique($ids))->order(['sort','create_time'])->select();
        }
    }

    /**
     * 获取树状列表
     * @param $list
     * @param int $parentId
     * @return array
     */
    public function getTreeData($list, int $parentId = 0): array
    {
        $data = [];
        foreach ($list as $key => $item) {
            if ($item['pid'] == $parentId) {
                $children = $this->getTreeData($list, $item['admin_menu_id']);
                !empty($children) && $item['children'] = $children;
                $data[] = $item;
                unset($list[$key]);
            }
        }
        return $data;
    }
}

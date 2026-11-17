<?php
declare (strict_types=1);

namespace app\common\model\admin;

use app\common\model\BaseModel;

/**
 * 后台部门模型
 * Class Dept
 * @package app\common\model\admin
 */
class Dept extends BaseModel
{
    // 定义表名
    protected $name = 'admin_dept';

    // 定义主键
    protected $pk = 'admin_dept_id';

    /**
     * 部门详情
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
    public function getList(array $param = []): \think\Collection
    {
        $list = $this->getAll($param);
        return $list;
    }

    /**
     * 获取所有记录
     * @param array $param
     * @return \think\Collection
     */
    protected function getAll(array $param = []): \think\Collection
    {
        // 默认查询参数
        $params = $this->setQueryDefaultValue($param, [
            'status' => 1,   // 状态(1显示 0隐藏 -1全部)
            'dept_name' => ''// 部门名称
        ]);
        // 设置检索条件
        $filter = [];
        $params['status'] > -1 && $filter[] = ['status', '=', $params['status']];
        !empty($params['dept_name']) && $filter[] = ['dept_name', 'like', "%{$params['dept_name']}%"];
        // 查询列表数据
        return $this->where($filter)
            ->order(['sort', 'create_time'])
            ->select();
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
                $children = $this->getTreeData($list, $item['admin_dept_id']);
                !empty($children) && $item['children'] = $children;
                $data[] = $item;
                unset($list[$key]);
            }
        }
        return $data;
    }
}

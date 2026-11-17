<?php
declare (strict_types = 1);

namespace app\admin\model\admin;

use app\common\model\admin\User as UserModel;
use think\facade\Request;

/**
 * 后台用户模型
 * Class Admin
 * @package app\admin\model\admin
 */
class Admin extends UserModel
{
    /** Explicit writable fields prevent primary-key and relation mass assignment. */
    public static function sanitize(array $data, bool $creating = false): array
    {
        $data = array_intersect_key($data, array_flip(['user_name','password','mobile','nick_name','avatar','gender','email','role_id','dept_id','status']));
        if ($creating && !array_key_exists('role_id', $data)) throw new \think\Exception('请选择有效角色',422);
        if (array_key_exists('role_id', $data)) {
            if ((!is_int($data['role_id']) && !is_string($data['role_id'])) || !ctype_digit((string)$data['role_id']) || (int)$data['role_id'] < 1) {
                throw new \think\Exception('请选择有效角色',422);
            }
            $data['role_id'] = (int)$data['role_id'];
        }
        foreach ($data as $field => $value) {
            if ($field !== 'avatar' && !is_scalar($value) && $value !== null) throw new \think\Exception('字段格式错误',422);
        }
        return $data;
    }
    /**
     * 隐藏的字段
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function getAvatarAttr($value)
    {
        return empty($value) ? [] : get_file_url($value);
    }
    public function setAvatarAttr($value)
    {
        if(is_array($value)){
            $ids = [];
            foreach ($value as $key => $item) {
                $ids[] = $item['id'];
            }
            return count($ids)?implode(',', $ids):0;
        }else{
            return $value;
        }
    }

    /**
     * 添加新记录
     * @param $data
     * @return bool
     */
    public function add($data): bool
    {
        $data = self::sanitize($data, true);
        if (strlen((string)($data['password'] ?? '')) < 12) throw new \think\Exception('密码至少12位',422);
        $data['password'] = encryption_hash($data['password']);
        if(isset($data['avatar']['id'])){
            $data['avatar'] = $data['avatar']['id'];
        }
        return $this->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool
     */
    public function edit($data): bool
    {
        $data = self::sanitize($data);
        if (empty($data['password'])) unset($data['password']);
        if(isset($data['password'])){
            if (strlen((string)$data['password']) < 12) throw new \think\Exception('密码至少12位',422);
            $data['password'] = encryption_hash($data['password']);
        }
        if(isset($data['avatar']['id'])){
            $data['avatar'] = $data['avatar']['id'];
        }
        return $this->save($data) !== false;
    }
    /**
     * 获取查询条件
     * @param array $param
     * @return array
     */
    private function getFilter(array $param = []): array
    {
        // 默认查询条件
        $params = $this->setQueryDefaultValue($param, [
            'name' => '',   // 用户名/昵称/手机号
            'dept_id' => 0, // 部门ID
            'status' => -1, // 状态
        ]);
        // 检索查询条件
        $filter = [];
        // 用户名/昵称/手机号
        !empty($params['name']) && $filter[] = ['user_name|nick_name|mobile', 'like', "%{$params['name']}%"];
        // 部门ID
        $params['dept_id'] > 0 && $filter[] = ['dept_id', '=', (int)$params['dept_id']];
        // 状态
        $params['status'] > -1 && $filter[] = ['status', '=', (int)$params['status']];
        // 起止时间
        if (!empty($params['betweenTime'])) {
            $times = between_time($params['betweenTime']);
            $filter[] = ['create_time', '>=', $times['start_time']];
            $filter[] = ['create_time', '<', $times['end_time'] + 86400];
        }
        return $filter;
    }
    /**
     * 获取用户列表
     * @param array $param
     * @return \think\Paginator
     */
    public function getList(array $param = []): \think\Paginator
    {
        // 检索查询条件
        $filter = $this->getFilter($param);
        // 获取用户列表
        return $this->with(['role', 'dept'])
            ->where($filter)
            ->order(['create_time' => 'desc'])
            ->paginate(input('pageSize',20,'intval'));
    }
}

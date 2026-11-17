<?php
declare (strict_types=1);

namespace app\common\model\admin;

use app\common\model\BaseModel;
use think\model\relation\HasMany;

/**
 * 后台角色模型
 * Class Role
 * @package app\common\model\admin
 */
class Role extends BaseModel
{
    // 定义表名
    protected $name = 'admin_role';

    // 定义主键
    protected $pk = 'admin_role_id';

    /**
     * 关联操作权限
     * @return HasMany
     */
    public function menu(): HasMany
    {
        return $this->hasMany('RoleMenu', 'role_id');
    }

    /**
     * 角色详情
     * @param int|array $where
     * @param array $with
     * @return static|array|null
     */
    public static function detail($where, array $with = [])
    {
        return static::find($where, $with);
    }
}
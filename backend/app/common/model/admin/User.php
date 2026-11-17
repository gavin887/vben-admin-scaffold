<?php
declare (strict_types = 1);

namespace app\common\model\admin;

use app\common\model\BaseModel;

/**
 * 后台用户模型
 * Class User
 * @package app\common\model\admin
 */
class User extends BaseModel
{
    // 定义表名
    protected $name = 'admin_user';

    // 定义主键
    protected $pk = 'admin_user_id';

    public function role()
    {
        return $this->belongsTo('Role', 'role_id', 'admin_role_id');
    }
    public function dept()
    {
        return $this->belongsTo('Dept', 'dept_id', 'admin_dept_id');
    }

    /**
     * 超管用户信息
     * @param int $id
     * @return static|array|null
     */
    public static function detail(int $id)
    {
        return static::find($id);
    }
}

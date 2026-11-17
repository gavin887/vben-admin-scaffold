<?php
declare (strict_types=1);

namespace app\common\model\admin;

use app\common\model\BaseModel;

/**
 * 用户角色与菜单权限关系模型
 * Class RoleAccess
 * @package app\common\model\admin
 */
class RoleMenu extends BaseModel
{
    // 定义表名
    protected $name = 'admin_role_menu';

    protected $updateTime = false;

}

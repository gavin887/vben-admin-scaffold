<?php
declare(strict_types=1);
namespace app\common\service;
use think\facade\Db;
/** Each management endpoint requires its own menu. */
class AdminAccess
{
    private const MENUS = ['admin'=>10004,'role'=>10006,'menu'=>10007,'dept'=>10008,'setting'=>10018];
    public static function allows(string $role, string $controller, string $action, array $menus): bool
    {
        if ($role === 'supperadmin') return true;
        if (in_array($controller, ['auth','index','upload'], true)) return true;
        if ($controller === 'menu' && in_array(strtolower($action), ['lists','trees'], true)) return true;
        if ($controller === 'role' && $action === 'all') return in_array(10004,$menus,true) || in_array(10006,$menus,true);
        if ($controller === 'dept' && $action === 'trees') return in_array(10004,$menus,true) || in_array(10008,$menus,true);
        return isset(self::MENUS[$controller]) && in_array(self::MENUS[$controller], $menus, true);
    }
    public static function check(int $roleId, string $controller, string $action, string $httpMethod): void
    {
        $role = Db::name('admin_role')->where(['admin_role_id'=>$roleId,'status'=>1])->find();
        if (!$role) throw new \think\Exception('permission denied',403);
        $menus = array_map('intval',Db::name('admin_role_menu')->where('role_id',$roleId)->column('menu_id'));
        if (!self::allows((string)$role['value'],$controller,$action,$menus)) throw new \think\Exception('permission denied',403);
    }
}

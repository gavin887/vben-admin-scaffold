<?php
declare (strict_types=1);

namespace app\admin\model\admin;

use app\common\model\admin\Menu as MenuModel;

/**
 * 后台菜单模型
 * Class Menu
 * @package app\admin\model\admin
 */
class Menu extends MenuModel
{
    public static function writable(array $data): array
    {
        return array_intersect_key($data, array_flip(['pid','path','name','component','redirect','meta','sort','status']));
    }
    /**
     * 添加新记录
     * @param $data
     * @return bool
     */
    public function add($data): bool
    {
        return $this->save(self::writable($data));
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool
     */
    public function edit($data): bool
    {
        return $this->save(self::writable($data)) !== false;
    }
}

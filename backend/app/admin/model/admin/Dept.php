<?php
declare (strict_types=1);

namespace app\admin\model\admin;

use app\common\model\admin\Dept as DeptModel;

/**
 * 后台部门模型
 * Class Dept
 * @package app\admin\model\admin
 */
class Dept extends DeptModel
{
    public static function writable(array $data): array
    {
        return array_intersect_key($data, array_flip(['pid','dept_name','sort','remark','status']));
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

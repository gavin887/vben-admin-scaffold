<?php
declare (strict_types=1);

namespace app\admin\model;

use think\facade\Cache;
use app\common\model\Setting as SettingModel;
use app\common\enum\Setting as SettingEnum;

/**
 * 系统设置模型
 * Class Setting
 * @package app\admin\model
 */
class Setting extends SettingModel
{
    /**
     * 更新系统设置
     * @param string $key
     * @param array $values
     * @return bool
     */
    public function edit(string $key, array $values): bool
    {
        if ($key !== 'storage' || ($values['default'] ?? '') !== 'local') {
            throw new \think\Exception('仅支持本地上传设置', 422);
        }
        $values = ['default'=>'local','engine'=>['local'=>null]];
        // 设置项详情记录
        $model = self::detail($key) ?: $this;
        // 删除系统设置缓存
        Cache::delete('setting');
        // 更新记录
        return $model->save([
                'key' => $key,
                'describe' => SettingEnum::data()[$key]['describe'],
                'values' => $values,
                'update_time' => time(),
            ]) !== false;
    }
}

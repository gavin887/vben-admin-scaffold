<?php
declare (strict_types=1);

namespace app\common\model;

use app\common\model\BaseModel;
use think\facade\Cache;
use app\common\enum\{
    Setting as SettingEnum,
    file\Storage as StorageEnum
};

/**
 * 系统设置模型
 * Class Setting
 * @package app\common\model
 */
class Setting extends BaseModel
{
    // 定义表名
    protected $name = 'setting';
    // 定义主键
    protected $pk = 'key';
    protected $createTime = false;
    protected $json = ['values'];
    protected $jsonAssoc = true;

    /**
     * 获取指定项设置
     * @param string $key
     * @return array|mixed
     */
    public static function getItem(string $key)
    {
        $data = self::getAll();
        return isset($data[$key]) ? $data[$key]['values'] : [];
    }

    /**
     * 获取设置项信息
     * @param string $key
     * @return static|array|null
     */
    public static function detail(string $key)
    {
        $query = (new static)->getNewQuery();
        return $query->where('key', '=', $key)->find();
    }

    /**
     * 全局缓存: 系统设置
     * @return array
     */
    public static function getAll(): array
    {
        $model = new static;
        if (!$data = Cache::get("setting")) {
            // 获取设置列表
            $setting = $model->getList();
            $data = $setting->isEmpty() ? [] : arrayColumn2Key($setting->toArray(), 'key');
            // 写入缓存中
            Cache::tag('cache')->set("setting", $data);
        }
        return resetOptions($model->defaultData(), $data);
    }

    /**
     * 获取设置列表
     * @return \think\Collection
     */
    private function getList(): \think\Collection
    {
        return $this->select();
    }

    /**
     * 默认配置
     * @return array
     */
    public function defaultData(): array
    {
        return ['storage'=>['key'=>'storage','describe'=>'本地上传设置','values'=>['default'=>'local','engine'=>['local'=>null]]]];
    }
}

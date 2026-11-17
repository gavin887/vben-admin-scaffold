<?php
declare (strict_types=1);

namespace app\admin\controller;

use think\response\Json;
use app\admin\model\Setting as SettingModel;

/**
 * 系统设置
 * Class Setting
 * @package app\admin\controller
 */
class Setting extends Controller
{
    /**
     * 全部记录
     * @return Json
     */
    public function all(): Json
    {
        $model = new SettingModel;
        $list = $model->field('key,describe,update_time')->select()->toArray();
        return $this->renderSuccess(['items'=>$list]);
    }

    /**
     * 获取设置项
     * @param string $key
     * @return Json
     */
    public function detail(string $key): Json
    {
        $values = SettingModel::getItem($key);
        return $this->renderSuccess(compact('values'));
    }

    /**
     * 更新系统设置
     * @param string $key
     * @return Json
     */
    public function edit(string $key): Json
    {
        $model = new SettingModel;
        if ($model->edit($key, $this->postForm())) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }
}

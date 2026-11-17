<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;
use think\db\Query;
use think\helper\Str;

/**
 * 模型基类
 * Class BaseModel
 * @package app\common\model
 */
abstract class BaseModel extends Model
{
    /**
     * 错误信息
     * @var string
     */
    protected string $error = '';

    // 定义表名
    protected $name;

    // 模型别名
    protected string $alias = '';

    /**
     * 模型基类初始化
     */
    public function init()
    {
        parent::init();
    }

    /**
     * 获取当前调用来源的应用名称
     * 例如：admin, api, store
     * @return string
     */
    protected final static function getCalledModule(): string
    {
        if (preg_match('/app\\\(\w+)/', get_called_class(), $class)) {
            return $class[1];
        }
        return 'common';
    }

    /**
     * 查找单条记录
     * @param mixed $data 查询条件
     * @param array $with 关联查询
     * @return array|static|null
     */
    public static function find($data, array $with = [])
    {
        try {
            $query = (new static)->with($with);
            return is_array($data) ? $query->where($data)->find() : $query->find((int)$data);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 获取当前表名称 (不含前缀)
     * @return string
     */
    public static final function getTableName(): string
    {
        $model = new static;
        return Str::snake($model->name);
    }

    /**
     * 设置默认的检索数据
     * @param array $query
     * @param array $default
     * @return array
     */
    public function setQueryDefaultValue(array $query, array $default = []): array
    {
        $data = array_merge($default, $query);

        foreach ($query as $field => $value) {
            // 不存在默认值跳出循环
            if (!isset($default[$field])) continue;
            // 如果传参为空, 设置默认值
            if (empty($value) && $value !== '0'&& $value !== 0) {
                $data[$field] = $default[$field];
            }
            if($value == 'undefined' || $value == 'null'){
                $data[$field] = $default[$field];
            }
        }
        return $data;
    }

    /**
     * 设置基础查询条件（用于简化基础alias和field）
     * @param string $alias
     * @param array $join
     * @return $this
     */
    public function setBaseQuery(string $alias = '', array $join = [])
    {
        // 设置别名
        $aliasValue = $alias ?: $this->alias;
        $query = $this->alias($aliasValue)->field("{$aliasValue}.*");
        // join条件
        if (!empty($join)) : foreach ($join as $item):
            $query->join($item[0], "{$item[0]}.{$item[1]}={$aliasValue}." . ($item[2] ?? $item[1]));
        endforeach; endif;
        return $query;
    }

    /**
     * 字段值增长
     * @param array|int|bool $where
     * @param string $field
     * @param float $step
     * @return mixed
     */
    protected function setInc($where, string $field, float $step = 1)
    {
        if (is_numeric($where)) {
            $where = [$this->getPk() => (int)$where];
        }
        return $this->where($where)->inc($field, $step)->update();
    }

    /**
     * 字段值消减
     * @param array|int|bool $where
     * @param string $field
     * @param float $step
     * @return mixed
     */
    protected function setDec($where, string $field, float $step = 1)
    {
        if (is_numeric($where)) {
            $where = [$this->getPk() => (int)$where];
        }
        return $this->where($where)->dec($field, $step)->update();
    }

    /**
     * 实例化新查询对象
     * @return \think\db\BaseQuery
     */
    protected function getNewQuery(): \think\db\BaseQuery
    {
        return $this->db();
    }

    /**
     * 新增hidden属性
     * @param array $hidden
     * @return $this
     */
    protected function addHidden(array $hidden): BaseModel
    {
        $this->hidden = array_merge($this->hidden, $hidden);
        return $this;
    }

    /**
     * 生成字段列表(字段加上$alias别名)
     * @param string $alias 别名
     * @param array $withoutFields 排除的字段
     * @return array
     */
    protected function getAliasFields(string $alias, array $withoutFields = []): array
    {
        $fields = array_diff($this->getTableFields(), $withoutFields);
        foreach ($fields as &$field) {
            $field = "$alias.$field";
        }
        return $fields;
    }

    /**
     * 获取隐藏的属性
     * @param array $hidden 合并的隐藏属性
     * @return array
     */
    public static function getHidden(array $hidden = []): array
    {
        return array_merge((new static)->hidden, $hidden);
    }

    /**
     * 设置错误信息
     * @param string $error
     * @return bool
     */
    protected function setError(string $error): bool
    {
        $this->error = $error ?: '未知错误';
        return false;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * 是否存在错误信息
     * @return bool
     */
    public function hasError(): bool
    {
        return !empty($this->error);
    }
}

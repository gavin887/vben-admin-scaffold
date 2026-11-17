<?php
declare (strict_types=1);

namespace app\common\model;

/**
 * 管理端审计日志模型 (只增不删, 永久保留)
 * Class AuditLog
 * @package app\common\model
 */
class AuditLog extends BaseModel
{
    // 定义表名
    protected $name = 'audit_log';

    // 定义主键
    protected $pk = 'id';
}

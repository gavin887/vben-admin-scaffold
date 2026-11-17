<?php
declare (strict_types=1);

namespace app\common\service;

use app\common\model\AuditLog as AuditLogModel;
use think\facade\Log;

/**
 * 审计日志服务
 * 高危操作强制记录: 操作人 / UTC 时间 / IP / 模块-动作 / 操作前后 JSON Diff
 * 记录失败不阻塞主流程, 仅落错误日志
 * Class AuditLog
 * @package app\common\service
 */
class AuditLog
{
    /**
     * 记录一条审计日志
     * @param int $adminUserId 操作人 (xfs_admin_user.id)
     * @param string $module 模块 (admin/setting/role/menu...)
     * @param string $action 动作 (add/edit/delete/setstatus...)
     * @param array|null $before 操作前数据
     * @param array|null $after 操作后数据
     * @return void
     */
    public static function record(int $adminUserId, string $module, string $action, ?array $before = null, ?array $after = null): void
    {
        try {
            (new AuditLogModel)->save([
                'admin_user_id' => $adminUserId,
                'module'        => $module,
                'action'        => $action,
                'before_json'   => $before === null ? '' : json_encode($before, JSON_UNESCAPED_UNICODE),
                'after_json'    => $after === null ? '' : json_encode($after, JSON_UNESCAPED_UNICODE),
                'ip'            => (string)request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[AuditLog] record failed: module=' . $module . ' action=' . $action . ' error=' . $e->getMessage());
        }
    }
}

<?php
declare (strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

/**
 * 数据库迁移状态查看命令 (只读)
 * 用法: php think migrate:status
 * 列出 database/migrations/*.sql 全部文件及状态 (已执行+batch+时间 / 待执行)
 * Class MigrateStatus
 * @package app\command
 */
class MigrateStatus extends Command
{
    protected function configure()
    {
        $this->setName('migrate:status')
            ->setDescription('Show status of SQL migrations in database/migrations/ (read-only)');
    }

    protected function execute(Input $input, Output $output)
    {
        $dir = root_path() . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            $output->writeln("<error>Migrations directory not found: {$dir}</error>");
            return 1;
        }

        $files = glob($dir . '*.sql') ?: [];
        sort($files, SORT_STRING);
        if (empty($files)) {
            $output->writeln('<comment>No migration files found in database/migrations/.</comment>');
            return 0;
        }

        // 追踪表: {prefix}migration (即 xfs_migration)
        $prefix = (string)config('database.connections.mysql.prefix');
        if (!preg_match('/^[a-zA-Z0-9_]*$/', $prefix)) throw new \RuntimeException('Invalid database prefix');
        $table = $prefix . 'migration';

        // 追踪表不存在时全部视为待执行 (只读命令, 不建表)
        $executed = [];
        $rows = Db::query(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name',
            ['name' => $table]
        );
        if (!empty($rows)) {
            foreach (Db::table($table)->select() as $row) {
                $executed[$row['migration']] = $row;
            }
        } else {
            $output->writeln("<comment>Tracking table `{$table}` does not exist yet. All files are pending.</comment>");
        }

        $output->writeln(sprintf('%-30s %-12s %-8s %s', 'Migration', 'Status', 'Batch', 'Run Time (UTC)'));
        $output->writeln(str_repeat('-', 78));

        $pendingCount = 0;
        foreach ($files as $file) {
            $name = basename($file);
            if (isset($executed[$name])) {
                $row = $executed[$name];
                $runTime = gmdate('Y-m-d H:i:s', (int)$row['run_time']);
                $output->writeln(sprintf(
                    '<info>%-30s %-12s %-8s %s</info>',
                    $name,
                    'Migrated',
                    (string)$row['batch'],
                    $runTime
                ));
            } else {
                $pendingCount++;
                $output->writeln(sprintf('<comment>%-30s %-12s %-8s %s</comment>', $name, 'Pending', '-', '-'));
            }
        }

        $output->writeln(str_repeat('-', 78));
        $output->writeln(sprintf(
            'Total: %d file(s), %d migrated, %d pending.',
            count($files),
            count($files) - $pendingCount,
            $pendingCount
        ));
        return 0;
    }
}

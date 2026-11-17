<?php
declare (strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

/**
 * 轻量数据库迁移执行命令 (替代 think-migration, 不依赖 composer)
 * 用法: php think migrate:run [--pretend]
 * 扫描 database/migrations/*.sql 按文件名升序执行, 追踪表 {prefix}migration
 * 注意: DDL 无法回滚, 单条语句失败即终止且不记录该文件, 需人工核查
 * Class MigrateRun
 * @package app\command
 */
class MigrateRun extends Command
{
    protected function configure()
    {
        $this->setName('migrate:run')
            ->addOption('pretend', null, Option::VALUE_NONE, 'List pending migration files without executing them')
            ->setDescription('Run pending SQL migrations in database/migrations/ (tracked in {prefix}migration)');
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

        $table = $this->getTrackTable();

        // 已执行清单 (追踪表不存在时视为全部待执行, pretend 模式下不建表)
        $executed = [];
        if ($this->trackTableExists($table)) {
            $executed = Db::table($table)->column('migration');
        }

        // 计算待执行文件
        $pending = [];
        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $executed, true)) {
                $output->writeln("<comment>Skipped : {$name} (already migrated)</comment>");
            } else {
                $pending[$name] = $file;
            }
        }

        if (empty($pending)) {
            $output->writeln('<info>Nothing to migrate. All files are up to date.</info>');
            return 0;
        }

        // --pretend: 仅列出将要执行的文件
        if ($input->getOption('pretend')) {
            $output->writeln('<info>Pretend mode. The following files would be executed (in order):</info>');
            foreach (array_keys($pending) as $name) {
                $output->writeln("  - {$name}");
            }
            return 0;
        }

        // 确保追踪表存在
        $this->ensureTrackTable($table);

        // 同一次运行共用一个 batch 号
        $batch = (int)Db::table($table)->max('batch') + 1;

        foreach ($pending as $name => $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                $output->writeln("<error>Failed to read file: {$name}</error>");
                return 1;
            }

            $content = str_replace('__PREFIX__', (string)config('database.connections.mysql.prefix'), $content);
            $statements = $this->splitStatements($content);
            if (empty($statements)) {
                $output->writeln("<comment>Skipped : {$name} (no executable statements)</comment>");
                // 空文件也记录, 避免每次重复扫描
                Db::table($table)->insert([
                    'migration' => $name,
                    'batch'     => $batch,
                    'run_time'  => time(),
                ]);
                continue;
            }

            $output->writeln("<info>Migrating: {$name} (" . count($statements) . ' statements)</info>');

            foreach ($statements as $index => $sql) {
                $seq = $index + 1;
                try {
                    Db::execute($sql);
                } catch (\Throwable $e) {
                    $output->writeln("<error>Failed  : {$name} at statement #{$seq}</error>");
                    $output->writeln('<error>SQL     : ' . mb_substr(trim($sql), 0, 200) . '</error>');
                    $output->writeln('<error>Error   : ' . $e->getMessage() . '</error>');
                    $output->writeln('<error>该文件已部分执行 (DDL 无法回滚), 未写入追踪记录, 需人工核查后重试。</error>');
                    return 1;
                }
            }

            // 全部成功后写入追踪记录
            Db::table($table)->insert([
                'migration' => $name,
                'batch'     => $batch,
                'run_time'  => time(),
            ]);
            $output->writeln("<info>Migrated : {$name} (batch {$batch})</info>");
        }

        $output->writeln('<info>All pending migrations executed successfully.</info>');
        return 0;
    }

    /**
     * 追踪表全名: {prefix}migration (即 xfs_migration)
     */
    protected function getTrackTable(): string
    {
        $prefix = (string)config('database.connections.mysql.prefix');
        if (!preg_match('/^[a-zA-Z0-9_]*$/', $prefix)) throw new \RuntimeException('Invalid database prefix');
        return $prefix . 'migration';
    }

    /**
     * 追踪表是否已存在 (information_schema, 避免 SHOW TABLES 不支持预处理占位符)
     */
    protected function trackTableExists(string $table): bool
    {
        $rows = Db::query(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name',
            ['name' => $table]
        );
        return !empty($rows);
    }

    /**
     * 追踪表不存在则自动创建
     */
    protected function ensureTrackTable(string $table): void
    {
        Db::execute(
            "CREATE TABLE IF NOT EXISTS `{$table}` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `migration` varchar(191) NOT NULL DEFAULT '' COMMENT '迁移文件名',
  `batch` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '批次号',
  `run_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '执行时间(UTC时间戳)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_migration` (`migration`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '数据库迁移追踪表'"
        );
    }

    /**
     * 按分号拆分 SQL 语句 (引号感知: 字符串/标识符内的分号不作为分隔符)
     * 过滤空语句与纯注释行 (`--` 开头); 本项目 SQL 无存储过程/触发器
     * @param string $content
     * @return string[]
     */
    protected function splitStatements(string $content): array
    {
        // 去掉 UTF-8 BOM, 统一换行
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $content = str_replace(["\r\n", "\r"], "\n", (string)$content);

        // 过滤纯注释行 (`--` 开头)
        $lines = [];
        foreach (explode("\n", $content) as $line) {
            if (str_starts_with(ltrim($line), '--')) {
                continue;
            }
            $lines[] = $line;
        }
        $content = implode("\n", $lines);

        // 引号感知拆分: 单引号/双引号/反引号内的分号不拆, 支持 \' 与 '' 转义
        $statements = [];
        $buffer = '';
        $quote = '';
        $len = strlen($content);
        for ($i = 0; $i < $len; $i++) {
            $char = $content[$i];
            if ($quote !== '') {
                $buffer .= $char;
                if ($char === '\\' && $quote !== '`') {
                    // 反斜杠转义: 连同下一字符一并入缓冲
                    if ($i + 1 < $len) {
                        $buffer .= $content[++$i];
                    }
                } elseif ($char === $quote) {
                    // '' / "" / `` 转义: 下一字符为同号引号则仍在引用内
                    if ($i + 1 < $len && $content[$i + 1] === $quote) {
                        $buffer .= $content[++$i];
                    } else {
                        $quote = '';
                    }
                }
                continue;
            }
            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $buffer .= $char;
                continue;
            }
            if ($char === ';') {
                $sql = trim($buffer);
                if ($sql !== '') {
                    $statements[] = $sql;
                }
                $buffer = '';
                continue;
            }
            $buffer .= $char;
        }
        $sql = trim($buffer);
        if ($sql !== '') {
            $statements[] = $sql;
        }
        return $statements;
    }
}

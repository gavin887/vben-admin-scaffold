<?php
declare (strict_types=1);

namespace app\common\enum\file;

/**
 * 枚举类：文件存储类型
 * Class Storage
 * @package app\common\enum\file
 */
class Storage
{
    // 本地
    const LOCAL = 'local';

    // 外部链接
    const EXTERNAL = 'external';

    /**
     * 获取枚举类型值
     * @return array
     */
    public static function data(): array
    {
        return [
            self::LOCAL => [
                'name' => '本地',
                'value' => self::LOCAL,
            ],
            self::EXTERNAL => [
                'name' => '外部链接',
                'value' => self::EXTERNAL,
            ]
        ];
    }
}

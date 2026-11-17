<?php
declare (strict_types=1);

namespace app\common\enum\file;

/**
 * 枚举类：文件类型
 * Class FileType
 * @package app\common\enum\file
 */
class FileType
{
    // 图片
    const IMAGE = 10;

    // 附件
    const ANNEX = 20;

    // 视频
    const VIDEO = 30;

    /**
     * 获取枚举类型值
     * @return array
     */
    public static function data(): array
    {
        return [
            self::IMAGE => [
                'name' => '图片',
                'value' => self::IMAGE,
            ],
            self::ANNEX => [
                'name' => '附件',
                'value' => self::ANNEX,
            ],
            self::VIDEO => [
                'name' => '视频',
                'value' => self::VIDEO,
            ]
        ];
    }

}

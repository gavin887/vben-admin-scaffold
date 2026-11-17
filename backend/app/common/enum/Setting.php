<?php
namespace app\common\enum;
class Setting { const STORAGE='storage'; public static function data(): array { return [self::STORAGE=>['value'=>'storage','describe'=>'本地上传设置']]; } }

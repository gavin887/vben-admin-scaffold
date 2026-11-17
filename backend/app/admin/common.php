<?php
// 这是系统自动生成的公共文件

/**
 * 日期转时间戳
 * @param string $date
 * @param bool $isWithTime 是否包含时间
 * @return false|int
 */
function str2date(string $date, bool $isWithTime = false)
{
    if (!$isWithTime) {
        $date = date('Y-m-d', strtotime($date));
    }
    return strtotime($date);
}

/**
 * 格式化起止时间
 * @param array $times
 * @param bool $isWithTime 是否包含时间
 * @return array
 */
function between_time(array|string $times, bool $isWithTime = false): array
{
    is_string($times) && $times = explode(',', $times);
    foreach ($times as &$time) {
        $time = trim($time, '&quot;');
        $time = str2date($time, $isWithTime);
    }
    return ['start_time' => current($times), 'end_time' => next($times)];
}
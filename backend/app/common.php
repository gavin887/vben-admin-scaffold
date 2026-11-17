<?php
// 应用公共文件
use think\facade\Env;
use think\facade\Request;
use think\facade\Db;
/**
 * 当前是否为调试模式
 * @return bool
 */
function is_debug(): bool
{
    return (bool)Env::instance()->get('APP_DEBUG');
}

/**
 * 驼峰转下划线
 * @param string $camelCaps
 * @param string $separator
 * @return string
 */
function uncamelize(string $camelCaps, string $separator = '_'): string
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}

/**
 * 生成密码hash值
 * @param string $password
 * @return string
 */
function encryption_hash(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * 资源URL转换
 * @param string|int $path
 * @return array
 */
function get_file_url(string|int|array $ids): array
{
    if(is_string($ids)) {
        $ids = explode(',', $ids);
    }
    if(is_numeric($ids)) {
        $ids = [$ids];
    }
    $files = Db::name('upload')->where('id', 'in', $ids)->column('*', 'id');
    $res = [];
    foreach ($ids as $id) {
        if (isset($files[$id]['storage']) && $files[$id]['storage'] == 'local') {
            $res[] = [
                'id'     => $files[$id]['id'],
                'name'   => $files[$id]['filename'],
                'status' => 'done',
                'url'    => Request::domain() . $files[$id]['url']
            ];
        } elseif (isset($files[$id]['storage'])) {
            // 非本地存储一律视为外部链接, 直接返回存储的 url
            $res[] = [
                'id'     => $files[$id]['id'],
                'name'   => $files[$id]['filename'],
                'status' => 'done',
                'url'    => $files[$id]['url']
            ];
        }
    }
    return $res;
}

function set_content_html(string $content): string
{
    return strtr($content, [Request::domain() => '____domain____']);
}
function get_content_html(string $content): string
{
    return strtr($content, ['____domain____' => Request::domain()]);
}

/**
 * 重组缓存数据（多维）
 * @param $data1
 * @param $data2
 * @return array
 */
function resetOptions($data1, $data2): array
{
    $data = [];
    foreach ($data1 + $data2 as $key => $val) {
        $data[$key] = (isset($data1[$key]) && is_array($data1[$key]) && isset($data2[$key]) && is_array($data2[$key]))
            ? (is_assoc($data1[$key]) ? resetOptions($data1[$key], $data2[$key]) : $data2[$key])
            : $data2[$key] ?? $data1[$key];
    }
    return $data;
}
/**
 * 判断是否为自定义索引数组
 * @param array $array
 * @return bool
 */
function is_assoc(array $array): bool
{
    if (empty($array)) return false;
    return array_keys($array) !== range(0, count($array) - 1);
}

/**
 * 把二维数组或对象中某列设置为key返回
 * @param $source
 * @param $index
 * @return mixed
 */
function arrayColumn2Key($source, $index)
{
    $data = [];
    foreach ($source as $item) {
        $data[$item[$index]] = $item;
    }
    return $data;
}

/**
 * 隐藏手机号中间四位 13012345678 -> 130****5678
 * @param string $mobile 手机号
 * @return string
 */
function hide_mobile(string $mobile): string
{
    return substr_replace($mobile, '****', 3, 4);
}

/**
 * 获取当前域名及根路径
 * @return string
 */
function base_url(): string
{
    static $baseUrl = '';
    if (empty($baseUrl)) {
        $request = Request::instance();
        // url协议，设置强制https或自动获取
        $scheme = $request->scheme();
        // url子目录
        $rootUrl = root_url();
        // 拼接完整url
        $baseUrl = "{$scheme}://" . $request->host() . $rootUrl;
    }
    return $baseUrl;
}

/**
 * 获取当前url的子目录路径
 * @return string
 */
function root_url(): string
{
    static $rootUrl = '';
    if (empty($rootUrl)) {
        $request = Request::instance();
        $subUrl = str_replace('\\', '/', dirname($request->baseFile()));
        $rootUrl = $subUrl . ($subUrl === '/' ? '' : '/');
    }
    return $rootUrl;
}
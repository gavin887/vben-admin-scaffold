<?php

namespace app\http\middleware;

use think\facade\Config;
use think\Request;
use think\Response;

/**
 * 跨域请求（CORS）处理中间件
 *
 * 开关、允许的域名、方法、请求头等均在 config/cors.php 中配置。
 * 负责下发 Access-Control-* 响应头，并对 OPTIONS 预检请求直接放行返回。
 */
class Cors
{
    public function handle(Request $request, \Closure $next)
    {
        $config  = Config::get('cors');
        $origin  = $request->header('origin');
        $headers = $this->buildCorsHeaders($config, $origin);

        // 未配置或未开启跨域支持，直接放行
        if (empty($config) || empty($config['enable'])) {
            return $next($request);
        }

        // 预检请求（OPTIONS）无需进入业务逻辑，直接返回 204
        if ($request->isOptions()) {
            return Response::create('', 'html', 204)->header($headers);
        }

        $response = $next($request);
        if ($response instanceof Response && !empty($headers)) {
            $response = $response->header($headers);
        }

        return $response;
    }

    /**
     * 计算当前请求对应的 CORS 响应头（供在中间件之外手动 send() 响应的场景复用）。
     *
     * 例如 DataDownload::verifyAndSend 采用「先响应后异步处理」，会在控制器内部
     * 直接 send() 并 fastcgi_finish_request()，此时本中间件的 $next 后置阶段尚未执行，
     * 无法自动补齐跨域头，需由调用方在 send() 之前显式附加。
     *
     * @param Request $request
     *
     * @return array
     */
    public static function corsHeaders(Request $request)
    {
        $config = Config::get('cors');
        if (empty($config) || empty($config['enable'])) {
            return [];
        }

        return (new self())->buildCorsHeaders($config, $request->header('origin'));
    }

    /**
     * 根据配置与来源构建 CORS 响应头
     *
     * @param array       $config
     * @param string|null $origin
     *
     * @return array
     */
    protected function buildCorsHeaders(array $config, $origin)
    {
        $headers = [];

        $allowOrigin = $this->resolveAllowOrigin($config, $origin);
        // 来源不在白名单内，不下发任何跨域头
        if (null === $allowOrigin) {
            return $headers;
        }

        $headers['Access-Control-Allow-Origin'] = $allowOrigin;
        // 命中具体域名时声明 Vary，避免不同来源共用缓存
        if ('*' !== $allowOrigin) {
            $headers['Vary'] = 'Origin';
        }

        $allowMethods                            = $config['allow_methods'] ?? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        $headers['Access-Control-Allow-Methods'] = implode(', ', $allowMethods);

        $allowHeaders                            = $config['allow_headers'] ?? ['*'];
        $headers['Access-Control-Allow-Headers'] = in_array('*', $allowHeaders, true)
            ? '*'
            : implode(', ', $allowHeaders);

        $exposeHeaders = $config['expose_headers'] ?? [];
        if (!empty($exposeHeaders)) {
            $headers['Access-Control-Expose-Headers'] = implode(', ', $exposeHeaders);
        }

        if (!empty($config['allow_credentials'])) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        if (isset($config['max_age'])) {
            $headers['Access-Control-Max-Age'] = (string)$config['max_age'];
        }

        return $headers;
    }

    /**
     * 计算 Access-Control-Allow-Origin 的取值
     *
     * @param array       $config
     * @param string|null $origin
     *
     * @return string|null 允许时返回具体来源或 '*'，不允许时返回 null
     */
    protected function resolveAllowOrigin(array $config, $origin)
    {
        $allowOrigins     = $config['allow_origins'] ?? ['*'];
        $allowCredentials = !empty($config['allow_credentials']);
        $origin           = is_string($origin) ? trim($origin) : '';

        // 允许所有来源
        if (in_array('*', $allowOrigins, true)) {
            // 携带凭证时不能使用通配符，需回显具体来源
            if ($allowCredentials) {
                return '' !== $origin ? $origin : null;
            }
            return '*';
        }

        // 精确匹配白名单
        if ('' !== $origin && in_array($origin, $allowOrigins, true)) {
            return $origin;
        }

        return null;
    }
}

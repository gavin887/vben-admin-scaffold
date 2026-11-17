<?php
namespace app\http\middleware;

use think\facade\Log;

class ApiLog
{
    public function handle($request, \Closure $next)
    {
        $started = microtime(true);
        $status = 500;
        try {
            $response = $next($request);
            $status = $response->getCode();
            return $response;
        } finally {
            // Metadata only: tokens and uploads stay out of logs.
            try {
                Log::write(json_encode([
                    'method' => $request->method(),
                    'path' => parse_url($request->url(), PHP_URL_PATH),
                    'status' => $status,
                    'duration_ms' => round((microtime(true) - $started) * 1000, 2),
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'info');
            } catch (\Throwable $ignored) {
                // Logging failures must not alter an already committed response.
            }
        }
    }
}

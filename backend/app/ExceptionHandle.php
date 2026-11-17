<?php
namespace app;
use think\exception\Handle;
use think\Response;
use Throwable;
class ExceptionHandle extends Handle
{
    public function render($request, Throwable $e): Response
    {
        $code = (int)$e->getCode();
        if (!in_array($code, [400, 401, 403, 404, 405, 422, 429], true)) $code = 500;
        $message = $code === 500 ? '服务器内部错误' : $e->getMessage();
        return json(['code'=>$code,'message'=>$message,'data'=>[]], $code);
    }
}

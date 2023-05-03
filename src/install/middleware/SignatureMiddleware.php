<?php

declare(strict_types=1);

namespace support\auth\middleware;

use Closure;
use mon\http\Jump;
use mon\env\Config;
use mon\http\Response;
use support\auth\SignatureService;
use mon\http\interfaces\RequestInterface;
use mon\http\interfaces\Middlewareinterface;

/**
 * signature权限校验中间件
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class SignatureMiddleware implements Middlewareinterface
{
    /**
     * 中间件实现接口
     *
     * @param RequestInterface $request  请求实例
     * @param Closure $callback 执行下一个中间件回调方法
     * @return Response
     */
    public function process(RequestInterface $request, Closure $callback): Response
    {
        // 中间件响应信息配置
        $responseConfig = Config::instance()->get('auth.signature.middleware.response');
        // 验证签名
        $check = SignatureService::instance()->checkToken($request->post());
        if (!$check) {
            // 不需要返回错误信息
            if (!$responseConfig['enable']) {
                return Jump::instance()->abort($responseConfig['status']);
            }

            // 错误码
            $code = SignatureService::instance()->getErrorCode();
            // 错误信息
            $msg = $responseConfig['message'] ? SignatureService::instance()->getError() : '';
            return Jump::instance()->result($code, $msg, [], [], $responseConfig['dataType'], $responseConfig['status']);
        }

        return $callback($request);
    }
}

<?php

declare(strict_types=1);

namespace support\auth\middleware;

use Closure;
use mon\http\Jump;
use mon\env\Config;
use mon\http\Response;
use support\auth\JwtService;
use mon\http\interfaces\RequestInterface;
use mon\http\interfaces\Middlewareinterface;

/**
 * JWT校验中间件
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class JwtMiddleware implements Middlewareinterface
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
        // 中间件配置
        $config = Config::instance()->get('auth.jwt.middleware');
        // 响应信息配置
        $responseConfig = $config['response'];

        // 获取Token
        $token = $request->header($config['header']);
        if (!$token) {
            // 不存在Token
            if (!$responseConfig['enable']) {
                return Jump::instance()->abort($config['noTokenStauts']);
            }

            // 错误信息
            $msg = $responseConfig['message'] ? $responseConfig['noTokenMsg'] : '';
            return Jump::instance()->result($responseConfig['noTokenCode'], $msg, [], [], $responseConfig['dataType'], $responseConfig['status']);
        }

        // 验证Token
        $check = JwtService::instance()->check($token);
        // Token验证不通过
        if (!$check) {
            // 不需要返回错误信息
            if (!$responseConfig['enable']) {
                return Jump::instance()->abort($responseConfig['status']);
            }

            // 错误码
            $code = JwtService::instance()->getErrorCode();
            // 错误信息
            $msg = $responseConfig['message'] ? JwtService::instance()->getError() : '';
            return Jump::instance()->result($code, $msg, [], [], $responseConfig['dataType'], $responseConfig['status']);
        }

        // 获取Token数据
        $data = JwtService::instance()->getData();
        // 记录用户ID
        $uid = $config['uid'];
        $request->$uid = $data['aud'];
        // 记录Token数据
        $jwt = $config['jwt'];
        $request->$jwt = $data;

        return $callback($request);
    }
}

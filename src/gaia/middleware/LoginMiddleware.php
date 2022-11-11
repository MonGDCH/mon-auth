<?php

declare(strict_types=1);

namespace support\auth\middleware;

use Closure;
use mon\http\Jump;
use mon\http\Response;
use support\auth\JwtService;
use mon\http\interfaces\RequestInterface;

/**
 * JWT登录校验中间件
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class LoginMiddleware implements \mon\http\interfaces\Middlewareinterface
{
    /**
     * 请求头token名
     *
     * @var string
     */
    protected $token_name = 'mon-wxapp-token';

    /**
     * 中间件实现接口
     *
     * @param RequestInterface $request  请求实例
     * @param Closure $callback 执行下一个中间件回调方法
     * @return Response
     */
    public function process(RequestInterface $request, Closure $callback): Response
    {
        // 验证token
        $token = $request->header($this->token_name);
        if (!$token) {
            // 未登录
            return Jump::instance()->abort(403);
        }
        // 判断token是否过期无效
        $info = JwtService::instance()->check($token);
        if (!$info) {
            // token过期
            return Jump::instance()->abort(401);
        }

        // 记录用户ID
        $request->uid = $info['sub'];

        return $callback($request);
    }
}

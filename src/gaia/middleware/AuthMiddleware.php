<?php

declare(strict_types=1);

namespace support\auth\middleware;

use Closure;
use mon\http\Jump;
use mon\http\Response;
use support\auth\RbacService;
use mon\http\interfaces\RequestInterface;

/**
 * RBAC权限校验中间件
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class AuthMiddleware implements \mon\http\interfaces\Middlewareinterface
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
        // 验证登录中间件
        if (!$request->uid) {
            // 未登录
            return Jump::instance()->abort(403);
        }
        // 验证权限
        if (!RbacService::instance()->check($request->path(), $request->uid)) {
            return Jump::instance()->abort(401);
        }

        return $callback($request);
    }
}

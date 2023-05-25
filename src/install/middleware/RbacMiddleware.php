<?php

declare(strict_types=1);

namespace support\auth\middleware;

use Closure;
use mon\http\Jump;
use mon\env\Config;
use mon\http\Response;
use support\auth\RbacService;
use mon\http\interfaces\RequestInterface;
use mon\http\interfaces\Middlewareinterface;

/**
 * RBAC权限校验中间件
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class RbacMiddleware implements Middlewareinterface
{
    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [];

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->config = array_merge($this->config, Config::instance()->get('auth.rbac.middleware', []));
    }

    /**
     * 获取配置信息
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 获取服务
     *
     * @return RbacService
     */
    public function getService(): RbacService
    {
        return RbacService::instance();
    }

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
        $config = $this->getConfig();
        // 响应信息配置
        $responseConfig = $config['response'];

        // 用户ID键名
        $uid = $config['uid'];
        // 验证登录
        if (!$request->$uid) {
            // 不存在用户ID，未登录
            if (!$responseConfig['enable']) {
                return Jump::instance()->abort($config['noLoginStatus']);
            }
            // 错误信息
            $msg = $responseConfig['message'] ? $responseConfig['noLoginMsg'] : '';
            return Jump::instance()->result($responseConfig['noLoginCode'], $msg, [], [], $responseConfig['dataType'], $responseConfig['status']);
        }

        // 验证权限
        $check = $this->getService()->check($request->path(), $request->$uid);
        // 权限验证不通过
        if (!$check) {
            // 不需要返回错误信息
            if (!$responseConfig['enable']) {
                return Jump::instance()->abort($responseConfig['status']);
            }

            // 错误码
            $code = $this->getService()->getErrorCode();
            // 错误信息
            $msg = $responseConfig['message'] ? $this->getService()->getError() : '';
            return Jump::instance()->result($code, $msg, [], [], $responseConfig['dataType'], $responseConfig['status']);
        }

        return $callback($request);
    }
}

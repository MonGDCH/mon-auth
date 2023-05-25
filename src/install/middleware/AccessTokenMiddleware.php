<?php

declare(strict_types=1);

namespace support\auth\middleware;

use Closure;
use mon\http\Jump;
use mon\env\Config;
use mon\http\Response;
use support\auth\AccessTokenService;
use mon\http\interfaces\RequestInterface;
use mon\http\interfaces\Middlewareinterface;

/**
 * AccessToken权限校验中间件
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class AccessTokenMiddleware implements Middlewareinterface
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
        $this->config = array_merge($this->config, Config::instance()->get('auth.accesstoken.middleware', []));
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
     * @return AccessTokenService
     */
    public function getService(): AccessTokenService
    {
        return AccessTokenService::instance();
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

        // 应用ID
        $appid = $this->getRequestData($request, $config['appid_name']);
        // Token
        $token = $this->getRequestData($request, $config['token_name']);

        // 验证参数
        if (empty($token) || empty($appid)) {
            // 不存在APPID或Token
            if (!$responseConfig['enable']) {
                return Jump::instance()->abort($config['noTokenStauts']);
            }

            // 错误信息
            $msg = $responseConfig['message'] ? $responseConfig['noTokenMsg'] : '';
            return Jump::instance()->result($responseConfig['noTokenCode'], $msg, [], [], $responseConfig['dataType'], $responseConfig['status']);
        }

        // 验证签名
        $check = $this->getService()->checkToken($token, $appid);
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

        // 获取Token中的数据
        $data = $this->getService()->getData();
        $key = $config['access_token'];
        $request->$key = $data;

        return $callback($request);
    }

    /**
     * 获取请求数据
     *
     * @param RequestInterface $request 请求实例
     * @param string $key   键名
     * @return mixed
     */
    protected function getRequestData(RequestInterface $request, string $key)
    {
        // 优先从post数据中获取
        $value = $request->post($key, null, false);
        if (is_null($value)) {
            // post中不存在，则从get获取
            $value = $request->get($key, null, false);
        }

        return $value;
    }
}

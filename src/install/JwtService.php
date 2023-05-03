<?php

declare(strict_types=1);

namespace support\auth;

use mon\env\Config;
use mon\util\Instance;
use mon\auth\jwt\Auth;
use mon\auth\exception\JwtException;

/**
 * JWT权限控制服务
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class JwtService
{
    use Instance;

    /**
     * 缓存服务对象
     *
     * @var Auth
     */
    protected $service;

    /**
     * Token数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * 错误信息
     *
     * @var string
     */
    protected $error = '';

    /**
     * 错误码
     *
     * @var integer
     */
    protected $errorCode = 0;

    /**
     * 私有构造方法
     */
    protected function __construct()
    {
        $config = Config::instance()->get('auth.jwt', []);
        $this->service = (new Auth)->init($config);
    }

    /**
     * 获取权限服务
     *
     * @return Auth
     */
    public function getService(): Auth
    {
        return $this->service;
    }

    /**
     * 获取Token数据
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError(): string
    {
        $error = $this->error;
        $this->error = '';
        return $error;
    }

    /**
     * 获取错误码
     *
     * @return integer
     */
    public function getErrorCode(): int
    {
        $code = $this->errorCode;
        $this->errorCode = 0;
        return $code;
    }

    /**
     * 注册配置信息
     *
     * @param array $config
     * @return JwtService
     */
    public function register(array $config): JwtService
    {
        $this->getService()->init($config);
        return $this;
    }

    /**
     * 创建Token
     *
     * @param integer|string $uid   用户ID
     * @param array $ext            扩展内容
     * @param integer|string $jti   TokenID
     * @throws JwtException
     * @return string
     */
    public function create($uid, array $ext = [], $jti = null): string
    {
        return $this->getService()->create($uid, $ext, $jti);
    }

    /**
     * 验证Token
     *
     * @param string $token Token
     * @return boolean
     */
    public function check(string $token): bool
    {
        try {
            // 解析获取Token数据，失败则抛出异常
            $this->data = $this->getService()->check($token);
            return true;
        } catch (JwtException $e) {
            $this->error = $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }
}

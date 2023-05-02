<?php

declare(strict_types=1);

namespace support\auth;

use mon\env\Config;
use mon\util\Instance;
use mon\auth\api\dao\DatabaseDao;
use mon\auth\exception\APIException;
use mon\auth\api\AccessTokenAuth as Auth;

/**
 * AccessToken权限控制服务
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class AccessTokenService
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
     * 构造方法
     */
    protected function __construct()
    {
        $config = Config::instance()->get('auth.accesstoken', []);
        $this->service = new Auth($this->parseConfig($config));
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
     * @param array $config 配置信息
     * @return AccessTokenService
     */
    public function register(array $config): AccessTokenService
    {
        $config = $this->parseConfig($config);
        $this->getService()->init($config);
        return $this;
    }

    /**
     * 创建AccessToken
     *
     * @param string $app_id    应用ID
     * @param string $secret    应用秘钥
     * @param array $extend     扩展数据
     * @throws APIException
     * @return string
     */
    public function create(string $app_id, string $secret, array $extend = []): string
    {
        return $this->getService()->create($app_id, $secret, $extend);
    }

    /**
     * 结合Dao数据创建AccessToken
     *
     * @param string $app_id    应用ID
     * @param array $extend     扩展数据
     * @throws APIException
     * @return string
     */
    public function createToken(string $app_id, array $extend = []): string
    {
        return $this->getService()->createToken($app_id, $extend);
    }

    /**
     * 校验AccessToken
     *
     * @param string $token token
     * @param string $app_id  应用ID
     * @param string $secret  应用秘钥
     * @throws APIException
     * @return boolean
     */
    public function check(string $token, string $app_id, string $secret): bool
    {
        try {
            // 解析获取Token数据，失败则抛出异常
            $this->data = $this->getService()->check($token, $app_id, $secret);
            return true;
        } catch (APIException $e) {
            $this->error = $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    /**
     * 校验AccessToken
     *
     * @param string $token token
     * @param string $app_id 应用ID
     * @return boolean
     */
    public function checkToken(string $token, string $app_id): bool
    {
        try {
            // 解析获取Token数据，失败则抛出异常
            $this->data = $this->getService()->checkToken($token, $app_id);
            return true;
        } catch (APIException $e) {
            $this->error = $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    /**
     * 解析完善配置信息
     *
     * @param array $config 配置信息
     * @return array
     */
    protected function parseConfig(array $config): array
    {
        if ($config['dao']['driver'] == DatabaseDao::class && is_string($config['dao']['construct']['config'])) {
            // 数据库dao驱动，字符串类型的数据库链接配置
            $dbconfig = Config::instance()->get('database.' . $config['dao']['construct']['config'], []);
            $config['dao']['construct']['config'] = $dbconfig;
        }

        return $config;
    }
}

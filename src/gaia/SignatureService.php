<?php

declare(strict_types=1);

namespace support\auth;

use mon\env\Config;
use mon\util\Instance;
use mon\auth\api\dao\DatabaseDao;
use mon\auth\exception\APIException;
use mon\auth\api\SignatureAuth as Auth;

/**
 * signature权限控制服务
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class SignatureService
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
        $config = Config::instance()->get('auth.signature', []);
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
     * @return SignatureService
     */
    public function register(array $config): SignatureService
    {
        $config = $this->parseConfig($config);
        $this->getService()->init($config);
        return $this;
    }

    /**
     * 获取应用信息
     *
     * @param string $app_id    应用ID
     * @return array
     */
    public function getAppInfo(string $app_id): array
    {
        try {
            return $this->getService()->getAppInfo($app_id);
        } catch (APIException $e) {
            $this->error = $e->getMessage();
            $this->errorCode = $e->getCode();
            return [];
        }
    }

    /**
     * 创建签名请求数据
     *
     * @param string $app_id    应用ID
     * @param string $secret    应用秘钥
     * @param array $data       需要签名的数据
     * @return array
     */
    public function create(string $app_id, string $secret, array $data = []): array
    {
        return $this->getService()->create($app_id, $secret, $data);
    }

    /**
     * 结合Dao数据创建API签名
     *
     * @param string $app_id    应用ID
     * @param array $data       需要签名的数据
     * @throws APIException
     * @return array
     */
    public function createToken(string $app_id, array $data = []): array
    {
        return $this->getService()->createToken($app_id, $data);
    }

    /**
     * 验证签名
     *
     * @param string $secret    应用秘钥
     * @param array $data       签名数据
     * @return boolean
     */
    public function check(string $secret, array $data): bool
    {
        try {
            $this->data = $data;
            // 解析获取Token数据，失败则抛出异常
            return $this->getService()->check($secret, $data);;
        } catch (APIException $e) {
            $this->error = $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    /**
     * 验证签名
     *
     * @param array $data   签名数据
     * @return boolean
     */
    public function checkToken(array $data): bool
    {
        try {
            // 解析获取Token数据，失败则抛出异常
            $this->data = $data;
            return $this->getService()->checkToken($data);;
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

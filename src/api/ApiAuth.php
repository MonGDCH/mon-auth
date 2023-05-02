<?php

namespace mon\auth\api;

use mon\auth\api\dao\DaoInterface;
use mon\auth\exception\APIException;
use mon\auth\api\driver\DriverInterface;

/**
 * API鉴权服务基类
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
abstract class ApiAuth implements ApiAuthInterface
{
    /**
     * 初始化标志
     *
     * @var boolean
     */
    protected $init = false;

    /**
     * 驱动实例
     *
     * @var DriverInterface
     */
    protected $driver;

    /**
     * Dao实例
     *
     * @var DaoInterface
     */
    protected $dao;

    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [];

    /**
     * 构造方法
     *
     * @param array $config 配置信息
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->init($config);
        }
    }

    /**
     * 初始化
     *
     * @param array $config 配置信息
     * @return ApiAuthInterface
     */
    public function init(array $config = []): ApiAuthInterface
    {
        // 定义配置
        $this->config = array_merge($this->config, $config);
        // 初始化API驱动
        $this->initDriver();
        // 初始化数据驱动
        $this->initDao();
        // 记录标志
        $this->init = true;

        return $this;
    }

    /**
     * 初始化API驱动
     *
     * @return void
     */
    abstract protected function initDriver();

    /**
     * 获取驱动实例
     *
     * @return DriverInterface
     */
    abstract public function getDriver(): DriverInterface;

    /**
     * 初始化Dao驱动
     *
     * @throws APIException
     * @return void
     */
    protected function initDao()
    {
        $config = $this->getConfig('dao');
        $driver = $config['driver'];
        if (!is_subclass_of($driver, DaoInterface::class)) {
            throw new APIException('Dao驱动未实现接口[' . DaoInterface::class . ']', APIException::DAO_NOT_SUPPORT);
        }

        $construct = $config['construct'];
        $this->dao = new $driver($construct);
    }

    /**
     * 获取Dao实例
     *
     * @return DaoInterface
     */
    public function getDao(): DaoInterface
    {
        return $this->dao;
    }

    /**
     * 是否初始化
     *
     * @return boolean
     */
    public function isInit(): bool
    {
        return $this->init;
    }

    /**
     * 获取配置信息
     *
     * @return mixed
     */
    public function getConfig(string $field = '')
    {
        if (!empty($field)) {
            return $this->config[$field];
        }

        return $this->config;
    }

    /**
     * 获取应用信息
     *
     * @param string $app_id    应用ID
     * @return array
     */
    public function getAppInfo(string $app_id): array
    {
        // 获取应用信息
        $info = $this->getDao()->getInfo($app_id);
        if (!$info) {
            throw new APIException('APPID不存在', APIException::APPID_NOT_FOUND);
        }
        if (!$this->getDao()->effect($info)) {
            throw new APIException('APPID无效', APIException::APPID_STATUS_ERROR);
        }
        if (!$this->getDao()->expire($info)) {
            throw new APIException('APPID已过期', APIException::APPID_TIME_INVALID);
        }

        return $info;
    }
}

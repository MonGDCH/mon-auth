<?php

declare(strict_types=1);

namespace mon\auth\api;

use mon\auth\api\dao\DaoInterface;
use mon\auth\api\driver\DriverInterface;

/**
 * API权限接口
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
interface ApiAuthInterface
{
    /**
     * 初始化
     *
     * @param array $config 配置信息
     * @return ApiAuthInterface
     */
    public function init(array $config = []): ApiAuthInterface;

    /**
     * 是否初始化
     *
     * @return boolean
     */
    public function isInit(): bool;

    /**
     * 获取配置信息
     *
     * @return mixed
     */
    public function getConfig(string $field = '');

    /**
     * 获取驱动实例
     *
     * @return DriverInterface
     */
    public function getDriver(): DriverInterface;

    /**
     * 获取Dao实例
     *
     * @return DaoInterface
     */
    public function getDao(): DaoInterface;
}

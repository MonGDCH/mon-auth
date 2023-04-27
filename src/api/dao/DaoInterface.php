<?php

namespace mon\auth\api\dao;

/**
 * Dao业务接口
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
interface DaoInterface
{
    /**
     * 获取所有数据
     *
     * @return array
     */
    public function getList(): array;

    /**
     * 获取指定APP_ID数据
     *
     * @param string $app_id    应用ID
     * @return array
     */
    public function getInfo(string $app_id): array;

    /**
     * 是否有效
     *
     * @param array $info   应用信息
     * @return boolean
     */
    public function effect(array $info): bool;

    /**
     * 是否在有效期内
     *
     * @param array $info   应用信息
     * @return boolean
     */
    public function expire(array $info): bool;
}

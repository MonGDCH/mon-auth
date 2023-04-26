<?php

namespace mon\auth\api\dao;

/**
 * Dao业务接口
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
}

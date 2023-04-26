<?php

declare(strict_types=1);

namespace mon\auth\api\dao;

use mon\orm\Model;

/**
 * 从database数据源中获取数据
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class DatabaseDao extends Model implements DaoInterface
{
    /**
     * 构造方法
     *
     * @param string $table 操作表名
     * @param array $config 数据库配置
     */
    public function __construct(string $table, array $config)
    {
        // 定义操作表
        $this->table = $table;
        // 定义数据库配置
        $this->config = $config;
    }

    /**
     * 获取所有数据
     *
     * @return array
     */
    public function getList(): array
    {
        return $this->where([])->select();
    }

    /**
     * 获取指定APP_ID数据
     *
     * @param string $app_id    应用ID
     * @return array
     */
    public function getInfo(string $app_id): array
    {
        return $this->where(['app_id' => $app_id])->find();
    }
}

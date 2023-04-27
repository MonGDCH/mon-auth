<?php

declare(strict_types=1);

namespace mon\auth\api\dao;

/**
 * 从Array数据源中获取数据
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class ArrayDao implements DaoInterface
{
    /**
     * 数据源
     *
     * @var array
     */
    private $data = [];

    /**
     * 构造方法
     *
     * @param array $config   配置信息
     */
    public function __construct(array $config)
    {
        $this->data = $config['data'];
    }

    /**
     * 获取所有数据
     *
     * @return array
     */
    public function getList(): array
    {
        return $this->data;
    }

    /**
     * 获取指定APP_ID数据
     *
     * @param string $app_id    应用ID
     * @return array
     */
    public function getInfo(string $app_id): array
    {
        $result = [];
        foreach ($this->data as $item) {
            if ($item['app_id'] == $app_id) {
                $result = $item;
                break;
            }
        }

        return $result;
    }

    /**
     * 是否有效
     *
     * @param array $info   应用信息
     * @return boolean
     */
    public function effect(array $info): bool
    {
        return $info['status'] == 1;
    }

    /**
     * 是否在有效期内
     *
     * @param array $info   应用信息
     * @return boolean
     */
    public function expire(array $info): bool
    {
        return ($info['expired_time'] == 0 || $info['expired_time'] > time());
    }
}

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
     * @param array $data   数据源
     */
    public function __construct(array $data)
    {
        $this->data = $data;
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
}

<?php
namespace mon\auth\rbac\model;

use mon\orm\Model;
use mon\env\Config;
use mon\auth\rbac\Validate;

/**
 * 模型基类
 */
class Comm extends Model
{
    /**
     * 新增自动写入字段
     *
     * @var array
     */
    protected $insert = ['create_time', 'update_time'];

    /**
     * 更新自动写入字段
     *
     * @var array
     */
    protected $update = ['update_time'];

    /**
     * 验证器
     *
     * @var [type]
     */
    protected $validate;

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->config = Config::instance()->get('mon_auth.database', []);
        $this->validate = new Validate;
    }

    /**
     * 自动完成update_time字段
     * 
     * @param [type] $val 默认值
     * @param array  $row 列值
     */
    protected function setUpdateTimeAttr($val)
    {
        return $_SERVER['REQUEST_TIME'];
    }

    /**
     * 自动完成create_time字段
     * 
     * @param [type] $val 默认值
     * @param array  $row 列值
     */
    protected function setCreateTimeAttr($val)
    {
        return $_SERVER['REQUEST_TIME'];
    }
}

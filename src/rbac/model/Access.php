<?php
namespace mon\auth\rbac\model;

use mon\auth\rbac\model\Comm;
use mon\auth\exception\RbacException;

/**
 * 组别用户关联模型
 */
class Access extends Comm
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'mon_auth_access';

    /**
     * 单例实现
     *
     * @var [type]
     */
    protected static $instance;

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
     * 获取单例
     *
     * @return void
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 创建组别用户关联
     *
     * @param array $option
     * @return void
     */
    public function add(array $option)
    {
        

        return true;
    }
}

<?php

namespace mon\auth\rbac\model;

use mon\orm\Model;
use mon\auth\rbac\Auth;
use mon\auth\rbac\Validate;
use mon\auth\exception\RbacException;

/**
 * 模型基类
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.1   优化代码
 */
class Base extends Model
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
     * @var Validate
     */
    protected $validate;

    /**
     * Auth实例
     *
     * @var Auth
     */
    protected $auth;

    /**
     * 构造方法
     *
     * @param Auth $auth Auth实例
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        if (!$this->auth->isInit()) {
            throw new RbacException('RBAC权限控制未初始化');
        }
        $this->config = $this->auth->getConfig('database');
        $this->validate = new Validate;
    }

    /**
     * 自动完成update_time字段
     * 
     * @param mixed $val 默认值
     * @param array  $row 列值
     * @return integer
     */
    protected function setUpdateTimeAttr($val)
    {
        return $_SERVER['REQUEST_TIME'];
    }

    /**
     * 自动完成create_time字段
     * 
     * @param mixed $val 默认值
     * @param array  $row 列值
     * @return integer
     */
    protected function setCreateTimeAttr($val)
    {
        return $_SERVER['REQUEST_TIME'];
    }
}

<?php

declare(strict_types=1);

namespace mon\auth\rbac\model;

use mon\orm\Model;
use mon\auth\rbac\Auth;
use mon\auth\rbac\Validate;
use mon\auth\exception\RbacException;

/**
 * 模型基类
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.1.0   优化代码
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
    protected $validate = Validate::class;

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
            throw new RbacException('权限服务未初始化', RbacException::AUTH_INIT_ERROR);
        }
        $this->config = $this->auth->getConfig('database');
    }

    /**
     * 自动完成update_time字段
     * 
     * @param mixed $val 默认值
     * @param array  $row 列值
     * @return integer
     */
    protected function setUpdateTimeAttr(): int
    {
        return time();
    }

    /**
     * 自动完成create_time字段
     * 
     * @param mixed $val 默认值
     * @param array  $row 列值
     * @return integer
     */
    protected function setCreateTimeAttr(): int
    {
        return time();
    }
}

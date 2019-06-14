<?php
namespace mon\auth\rbac\model;

use mon\util\Instance;
use mon\auth\rbac\Validate;
use mon\auth\rbac\model\Comm;

/**
 * 权限规则表
 */
class Rule extends Comm
{
    use Instance;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'mon_auth_rule';

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
        parent::__construct();
        $this->validate = new Validate;
    }
}

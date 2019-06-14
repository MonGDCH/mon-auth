<?php
namespace mon\auth\rbac\model;

use mon\util\Instance;
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
     * 获取规则信息
     *
     * @param integer $gid
     * @return void
     */
    public function getInfo(int $rule_id)
    {
        return $this->where('id', $rule_id)->get();
    }

    /**
     * 新增规则
     *
     * @param arrry $option
     * @return void
     */
    public function add(arrry $option)
    {
        $check = $this->validate->scope('group_add')->data($option)->check();
        if ($check !== true) {
            $this->error = $check;
            return false;
        }
    }
}

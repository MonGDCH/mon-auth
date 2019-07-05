<?php
namespace mon\auth\rbac;

use mon\util\Validate as Vali;

/**
 * RBAC验证器
 */
class Validate extends Vali
{
    /**
     * 验证规则
     *
     * @var array
     */
    public $rule = [
        'idx'           => 'required|int|min:1',
        'uid'           => 'required|int|min:1',
        'gid'           => 'required|int|min:1',
        'pid'           => 'required|int|min:0',
        'status'        => 'required|in:0,1',
        'rules'         => 'required|str|rules',
        'name'          => 'required|str',
        'mark'          => 'required|str',
        'description'   => 'str',
        'offset'        => 'int|min:0',
        'limit'         => 'int|min:1',
        'start_time'    => 'timestamp',
        'end_time'      => 'timestamp',
    ];

    /**
     * 错误提示信息
     *
     * @var [type]
     */
    public $message = [
        'idx'           => 'ID格式错误',
        'uid'           => '用户ID格式错误',
        'gid'           => '组别ID格式错误',
        'pid'           => '上级ID格式错误',
        'rules'         => '规则组格式错误',
        'name'          => '名称格式错误',
        'mark'          => '规则标志格式错误',
        'description'   => '附加信息格式错误',
        'offset'        => 'offset格式错误',
        'limit'         => 'limit格式错误',
        'status'        => '状态参数错误'
    ];

    /**
     * 验证场景
     *
     * @var [type]
     */
    public $scope = [
        // 分配用户组别关联
        'access_add'    => ['uid', 'gid'],
        // 修改组别用户关联
        'access_modify' => ['uid', 'gid'],
        // 添加角色组别
        'group_add'     => ['pid', 'name', 'rules'],
        // 修改角色组别信息
        'group_modify'  => ['idx', 'pid', 'name', 'rules'],
        // 增加规则
        'rule_add'      => ['mark', 'pid', 'name', 'description'],
        // 修改规则
        'rule_modify'   => ['mark', 'pid', 'name', 'description', 'idx', 'status'],
    ];

    /**
     * 验证规则组数据
     *
     * @param string $value
     * @return void
     */
    public function rules($value)
    {
        if (empty($value)) {
            return false;
        }
        $rules = explode(',', $value);
        foreach ($rules as $rule) {
            if (!$this->int($rule)) {
                return false;
            }
        }

        return true;
    }
}

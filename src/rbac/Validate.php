<?php

declare(strict_types=1);

namespace mon\auth\rbac;

/**
 * RBAC验证器
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.1   优化代码
 */
class Validate extends \mon\util\Validate
{
    /**
     * 验证规则
     *
     * @var array
     */
    public $rule = [
        'idx'           => ['required', 'int', 'min:1'],
        'uid'           => ['required', 'int', 'min:1'],
        'gid'           => ['required', 'int', 'min:1'],
        'new_gid'       => ['required', 'int', 'min:1'],
        'pid'           => ['required', 'int', 'min:0'],
        'status'        => ['required', 'int', 'min:0'],
        'name'          => ['required', 'str'],
        'title'         => ['required', 'str'],
        'rules'         => ['arr', 'rules'],
        'remark'        => ['str'],
        'offset'        => ['int', 'min:0'],
        'limit'         => ['int', 'min:1'],
        'start_time'    => ['timestamp'],
        'end_time'      => ['timestamp'],
    ];

    /**
     * 错误提示信息
     *
     * @var array
     */
    public $message = [
        'idx'           => 'ID格式错误',
        'uid'           => '用户ID格式错误',
        'gid'           => '组别ID格式错误',
        'new_gid'       => '新组别ID格式错误',
        'pid'           => '上级ID格式错误',
        'rules'         => '角色组别规则格式错误',
        'name'          => '规则标志格式错误',
        'title'         => '规则名称格式错误',
        'remark'        => '附加信息格式错误',
        'offset'        => 'offset格式错误',
        'limit'         => 'limit格式错误',
        'status'        => '状态参数错误'
    ];

    /**
     * 验证场景
     *
     * @var array
     */
    public $scope = [
        // 绑定用户组
        'access_bind'       => ['uid', 'gid'],
        // 解除绑定角色组
        'access_unbind'     => ['uid', 'gid'],
        // 修改组别用户关联
        'access_modify'     => ['uid', 'gid', 'new_gid'],
        // 添加角色组别
        'group_add'         => ['pid', 'title', 'rules'],
        // 修改角色组别信息
        'group_modify'      => ['idx', 'pid', 'title', 'rules', 'status'],
        // 增加规则
        'rule_add'          => ['title', 'pid', 'name', 'remark'],
        // 修改规则
        'rule_modify'       => ['title', 'pid', 'name', 'remark', 'idx', 'status'],
    ];

    /**
     * 验证规则组数据
     *
     * @param array $value
     * @return boolean
     */
    public function rules(array $value): bool
    {
        foreach ($value as $rule) {
            if (!$this->int($rule)) {
                return false;
            }
        }

        return true;
    }
}

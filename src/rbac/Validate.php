<?php
namespace mon\auth\rbac\model;

use mon\util\Validate as Vali;

/**
 * RBAC验证器
 */
class Validate extends Vali
{
    /**
     * 严重规则
     *
     * @var array
     */
    public $rule = [
        'uid'       => 'required|int|min:1',
        'gid'       => 'required|int|min:1',
        'rule_id'       => 'required|int|min:1',
    ];
}
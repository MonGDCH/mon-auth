<?php
/*
|--------------------------------------------------------------------------
| RBAC权限控制配置文件
|--------------------------------------------------------------------------
| 定义RBAC权限控制配置信息
|
*/

return [
    // 权限开关
    'enable'            => true,
    // 用户组数据表名     
    'auth_group'        => 'auth_group',
    // 用户-用户组关系表
    'auth_group_access' => 'auth_access',
    // 权限规则表
    'auth_rule'         => 'auth_rule',
    // 超级管理员权限标志
    'admin_mark'        => '*',
    // 数据库配置，采用 database.default 配置            
    'database'          => 'default'
];

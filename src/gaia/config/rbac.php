<?php
/*
|--------------------------------------------------------------------------
| RBAC权限控制配置文件
|--------------------------------------------------------------------------
| 定义RBAC权限控制配置信息
|
*/

return [
    // 用户组数据表名     
    'auth_group'        => 'auth_group',
    // 用户-用户组关系表
    'auth_group_access' => 'auth_access',
    // 权限规则表
    'auth_rule'         => 'auth_rule',
    // 超级管理员权限标志
    'admin_mark'        => '*',
    // 数据库配置，采用 database.default 配置            
    'database'          => 'default',
    // 中间件配置
    'middleware'    => [
        // Request实例中用户ID的属性名
        'uid'   => 'uid',
        // 未登录HTTP状态码
        'noLoginStatus' => 401,
        // 错误信息
        'response'  => [
            // 是否返回错误信息
            'enable'        => true,
            // HTTP状态码
            'status'        => 403,
            // 返回数据类型, json 或 xml
            'dataType'      => 'json',
            // 是否输出错误信息, enable 为 true 时有效
            'message'       => false,
            // 未登录返回状态码
            'noLoginCode'   => 401,
            // 未登录错误信息，message 为 true 时有效
            'noLoginMsg'    => '未登录',
        ],
    ],
];

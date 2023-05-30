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
    // 有效的状态值
    'effective_status'  => 1,
    // 无效的状态值
    'invalid_status'    => 0,
    // 数据库配置              
    'database'          => [
        // 数据库类型，只支持mysql
        'type'          => 'mysql',
        // 服务器地址
        'host'          => '127.0.0.1',
        // 数据库名
        'database'      => '',
        // 用户名
        'username'      => '',
        // 密码
        'password'      => '',
        // 端口
        'port'          => '3306',
        // 数据库连接参数
        'params'        => [],
        // 数据库编码默认采用utf8
        'charset'       => 'utf8mb4',
        // 返回结果集类型
        'result_type'   => \PDO::FETCH_ASSOC,
        // 是否开启读写分离
        'rw_separate'   => false,
        // 查询数据库连接配置，二维数组随机获取节点覆盖默认配置信息
        'read'          => [],
        // 写入数据库连接配置，同上，开启事务后，读取不会调用查询数据库配置
        'write'         => []
    ],
];

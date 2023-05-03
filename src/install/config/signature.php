<?php

return [
    // 默认加密盐
    'salt'  => '%s',
    // 字段映射
    'field' => [
        // app_id字段名
        'app_id'    => 'app_id',
        // 签名字段名
        'signature' => 'signature',
        // 签名时间字段名
        'timestamp' => 'timestamp',
        // 随机字符串字段名
        'noncestr'  => 'noncestr',
        // secret key名
        'secret'    => 'key'
    ],
    // 有效时间，单位秒
    'expire'    => 3600,
    // 数据源配置
    'dao'      => [
        // 驱动，默认数组驱动
        'driver'    => \mon\auth\api\dao\DatabaseDao::class,
        // 构造方法传入参数
        'construct'    => [
            // 数组驱动APP应用数据列表，driver驱动为 ArrayDao 时有效
            'data'      => [],
            // 数据库驱动操作表，driver驱动为 DatabaseDao 时有效
            'table'     => 'api_sign',
            // 数据库链接配置，driver驱动为 DatabaseDao 时有效
            'config'    => 'default'
        ]
    ],
    // 中间件配置
    'middleware'    => [
        // 错误信息
        'response'      => [
            // 是否返回错误信息
            'enable'        => true,
            // HTTP状态码
            'status'        => 400,
            // 返回数据类型, json 或 xml
            'dataType'      => 'json',
            // 是否输出错误信息, enable 为 true 时有效
            'message'       => false,
        ],
    ],
];

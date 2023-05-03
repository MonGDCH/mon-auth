<?php
/*
|--------------------------------------------------------------------------
| JWT权限控制配置文件
|--------------------------------------------------------------------------
| 定义JWT权限控制配置信息
|
*/

return [
    // 加密key
    'key'       => '%s',
    // 加密算法
    'alg'       => 'HS256',
    // 签发单位
    'iss'       => 'Gaia-Auth',
    // 签发主题
    'sub'       => 'User-Auth',
    // 生效时间，签发时间 + nbf
    'nbf'       => 0,
    // 有效时间，生效时间 + exp
    'exp'       => 3600,
    // 中间件配置
    'middleware'    => [
        // 请求头token名
        'header'        => 'Mon-Auth-Token',
        // 用户ID(aud)在Request实例的属性名
        'uid'           => 'uid',
        // Token数据在Request实例的属性名
        'jwt'           => 'jwt',
        // 不存在Token的HTTP状态码
        'noTokenStauts' => 400,
        // 错误信息
        'response'      => [
            // 是否返回错误信息
            'enable'        => true,
            // HTTP状态码
            'status'        => 403,
            // 返回数据类型, json 或 xml
            'dataType'      => 'json',
            // 是否输出错误信息, enable 为 true 时有效
            'message'       => false,
            // 未登录返回状态码
            'noTokenCode'   => 400,
            // 未登录错误信息，message 为 true 时有效
            'noTokenMsg'    => 'Token params invalid!',
        ]
    ],
];

<?php

use mon\auth\api\SignatureAuth;

require_once __DIR__ . '/../vendor/autoload.php';


SignatureAuth::instance()->init([
    // 数据源配置
    'dao'      => [
        // 驱动，默认数组驱动
        'driver'    => \mon\auth\api\dao\DatabaseDao::class,
        // 构造方法传入参数
        'construct'    => [
            // 数据库驱动操作表，驱动为 DatabaseDao 时有效
            'table'     => 'api_sign',
            // 数据库链接配置，驱动为 DatabaseDao 时有效
            'config'    => [
                // 数据库类型，只支持mysql
                'type'          => 'mysql',
                // 服务器地址
                'host'          => '127.0.0.1',
                // 数据库名
                'database'      => 'test',
                // 用户名
                'username'      => 'root',
                // 密码
                'password'      => '123456',
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
            ]
        ]
    ]
]);


$appid = 'TEST123456789';

$data = [
    'a' => 1,
    'b' => 'asd',
    'c' => true,
];

$tokenData = SignatureAuth::instance()->createToken($appid, $data);

dd($tokenData);


$check = SignatureAuth::instance()->checkToken($tokenData);
dd($check);

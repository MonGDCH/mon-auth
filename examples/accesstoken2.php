<?php

use mon\auth\api\AccessTokenAuth;

require_once __DIR__ . '/../vendor/autoload.php';

// 初始化
AccessTokenAuth::instance()->init();

$appid = 'TEST123456789';

$token = AccessTokenAuth::instance()->createToken($appid, [
    's1'    => 'yadan',
    's2'    => 'xiawa'
]);
dd($token);

$data = AccessTokenAuth::instance()->checkToken($token, $appid);
dd($data);

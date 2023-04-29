<?php

use mon\util\Event;
use mon\auth\api\AccessTokenAuth;
use mon\auth\exception\APIException;

require_once __DIR__ . '/../vendor/autoload.php';

// 初始化
AccessTokenAuth::instance()->init();

$appid = 'abcdefg';
$secret = 'asdas234';

// 自定义验证事件
Event::instance()->listen('access_check', function ($data) {
    // token数据
    // dd($data);

    // 抛出异常 APIException 作为验证不通过的标志
    throw new APIException('自定义验证错误', 0, null, $data);
});



$token = AccessTokenAuth::instance()->create($appid, $secret);

dd($token);

try {
    $decode = AccessTokenAuth::instance()->check($token, $appid, $secret);
    dd($decode);
} catch (APIException $e) {
    dd('验证不通过！' . $e->getMessage() . ' code: ' . $e->getCode());
    // 异常绑定的数据
    dd($e->getData());
}

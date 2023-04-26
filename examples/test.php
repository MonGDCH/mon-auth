<?php

use mon\auth\api\AccessToken;
use mon\auth\api\Sign;
use nbed64\Nbed64;

require __DIR__ . '/../vendor/autoload.php';

$app_id = '123456';

$secret = 'abcdefg';

$salt = 'aaaa';

$token = AccessToken::instance()->create('1456123', $salt, 10, ['name' => '张三']);

// dd($token);

// $str = 'WVlYcHNTU0ZkRnBOVHJkSmJhVVozR1kwWWM1Y0ZqcERTTFRaSlNha1VRd1RsbFJaZWFFMXFYVFdsVk1RMGZwc1piVWhCM2FVOWNxUlR4SlBSTEVsNmpUMFJBcmVVNDVxVVlqbE1XYmtKSklaRGhGRlBZUWkwMGlpMDBpdw==';


$dd = 'GkMPQExESVFJjdTIg9_uAQ8BTF1de01ABx8HWF1cQ0RHbH1zTQoXB1VNAAcJHB0XAgUDDEU4';
$decode = AccessToken::instance()->parse($token, $salt);
// dd($decode);


// $check = AccessToken::instance()->check($str, $salt);
// dd($check);


$data = [
    'aa'    => 123,
    'name'  => '你好',
    'bb'    => 'asdf'
];

$sign = Sign::instance()->create($app_id, $secret, $data);
// $sign = Sign::instance()->getSign($data, $secret);
dd($sign);

$check = Sign::instance()->check($secret, $sign);
dd($check);

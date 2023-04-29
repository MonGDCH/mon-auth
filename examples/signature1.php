<?php

use mon\auth\api\SignatureAuth;

require_once __DIR__ . '/../vendor/autoload.php';


SignatureAuth::instance()->init();


$appid = 'TEST123456789';

$secret = 'asdas234';


$data = [
    'a' => 1,
    'b' => 'asd',
    'c' => true,
];

$tokenData = SignatureAuth::instance()->create($appid, $secret, $data);

dd($tokenData);


$check = SignatureAuth::instance()->check($secret, $tokenData);

dd($check);
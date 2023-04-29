<?php

require __DIR__ . '/../vendor/autoload.php';

use mon\auth\jwt\driver\Token;
use mon\auth\jwt\driver\Payload;
use mon\auth\exception\JwtException;

try{
	// 加密密钥
	$key = 'aaaaaaa';
	// 加密算法
	$alg = 'HS256';
	$build = new Payload;
	// $token = new Token;
	$token = Token::instance();

	// 构建payload
	$payload = $build->setIss('abc')->setSub('def')->setExt(['a' => '123'])->setExp(3600)->setAud('127.0.0.1');
	// 创建jwt
	$jwt = $token->create($payload, $key, $alg);
	var_dump($jwt);

	// 验证jwt
	$data = $token->check($jwt, $key, $alg);
	var_dump($data);
}
catch (JwtException $e){
	var_dump('Msg: '.$e->getMessage(), 'Line: '.$e->getLine(), 'Code: '.$e->getCode());
}

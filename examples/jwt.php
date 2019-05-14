<?php

require __DIR__ . '/../vendor/autoload.php';

use mon\auth\Token;
use mon\auth\exception\JwtException;

try{
	$key = 'aaaaaaa';
	$token = new Token;

	$jwt = $token->setIss('abc')->setSub('def')->setExt(['a' => '123'])->setExp(3600)->setAud('asdf')->create($key);

	var_dump($jwt);

	$data = $token->check($jwt, $key);

	var_dump($data);
}
catch (JwtException $e){
	var_dump('Msg: '.$e->getMessage(), 'Line: '.$e->getLine(), 'Code: '.$e->getCode());
}

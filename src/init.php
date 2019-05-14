<?php

use mon\env\Config;

// 定义加载配置
$configFile = __DIR__ . '/../config/config.php';
$config = Config::instance()->get('mon_auth');
if(!$config){
	Config::instance()->load($configFile, 'mon_auth');
}
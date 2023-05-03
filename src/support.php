<?php

/*
|--------------------------------------------------------------------------
| 初始化支持文件
|--------------------------------------------------------------------------
*/


// Gaia环境，进行指令注册
if (PHP_SAPI == 'cli' && class_exists(\gaia\App::class)) {
    $path = __DIR__ . '/command';
    $namespance = 'mon\\auth\\command';
    \gaia\App::console()->load($path, $namespance);
}

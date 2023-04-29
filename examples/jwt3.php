<?php

use mon\auth\jwt\Auth;

require __DIR__ . '/../vendor/autoload.php';

$token = Auth::instance()->create(1, ['pm' => 'tch']);

dd($token);

$data = Auth::instance()->check($token);
dd($data);
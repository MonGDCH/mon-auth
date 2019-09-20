<?php

use mon\auth\rbac\Auth;
use mon\auth\rbac\model\Access;
use mon\auth\rbac\model\Group;
use mon\auth\rbac\model\Rule;
use mon\util\Common;

require __DIR__ . '/../vendor/autoload.php';

$auth = Auth::instance();
$auth->init(['database' => [
    // 服务器地址
    'host'            => '127.0.0.1',
    // 数据库名
    'database'        => 'test',
    // 用户名
    'username'        => 'root',
    // 密码
    'password'        => 'root',
    // 端口
    'port'            => '3306',
]]);

// 新增规则
// $save = Rule::instance()->add([
//     'title'     => 't5',
//     'name'      => 't5',
//     // 'remark'    => 't3_remark',
//     'pid'       => 6
// ]);

// 修改规则
// $save = Rule::instance()->modify([
//     'title'     => 'tts',
//     'name'      => 'tts',
//     'remark'    => 'tts_remark',
//     'pid'       => 0,
//     'status'    => 1,
//     'idx'       => 6
// ]);

// 新增组别
// $save = Group::instance()->add([
//     'pid'   => 12,
//     'title' => 'tests',
//     'rules' => [8, 6]
// ]);

// 更新组别
// $save = Group::instance()->modify([
//     'idx'   => 12,
//     'pid'   => 2,
//     'title' => 'demo1',
//     'rules' => [8, 7, 9],
//     'status'=> 1,
// ]);

// $access = Access::instance()->modify([
//     'uid'   => 1,
//     'gid'   => 1,
//     'new_gid'   => '12'
// ]);

// $access = Access::instance()->getUserGroup(2);


// $data = Auth::instance()->getAuthIds(1);

// $data = Auth::instance()->getAuthList(1);

// $data = Auth::instance()->getRule(1);

$check = Auth::instance()->check('t5,t4,55', 1, true);

var_dump($check);




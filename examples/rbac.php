<?php

use mon\auth\rbac\Auth;
use mon\orm\exception\DbException;

require __DIR__ . '/../vendor/autoload.php';

$config = ['database' => [
    // 服务器地址
    'host'            => '127.0.0.1',
    // 数据库名
    'database'        => 'record',
    // 用户名
    'username'        => 'root',
    // 密码
    'password'        => '19930603',
    // 端口
    'port'            => '3306',
]];

// $auth = Auth::instance();
// $auth->init(['database' => [
//     // 服务器地址
//     'host'            => '127.0.0.1',
//     // 数据库名
//     'database'        => 'test',
//     // 用户名
//     'username'        => 'root',
//     // 密码
//     'password'        => 'root',
//     // 端口
//     'port'            => '3306',
// ]]);

$auth2 = new Auth();
$auth2->init($config);

// // 新增规则
// $save = $auth->model('rule')->add([
//     'title'     => 't',
//     'name'      => 't7',
//     'remark'    => 't3_remark',
//     'pid'       => 0
// ]);


// 修改规则
// $save = $auth->model('rule')->modify([
//     'title'     => 'ttsx',
//     'name'      => '123asda',
//     'remark'    => '',
//     'pid'       => 0,
//     'status'    => 1,
//     'idx'       => 1
// ]);


// 新增组别
// $save = $auth->model('group')->add([
//     'pid'   => 0,
//     'title' => 'testsss',
//     'rules' => [8, 6]
// ]);


// 更新组别
// $save = $auth->model('group')->modify([
//     'idx'   => 1,
//     'pid'   => 0,
//     'title' => 'demo1',
//     'rules' => [8, 7, 9],
//     'status'=> 1,
// ]);

// var_dump($save, $auth->model('group')->getError());exit;


// var_dump($save);
// var_dump($auth->model('group')->getError());

// 绑定用户组别
// $access = $auth2->model('access')->bind([
//     'uid'   => 10,
//     'gid'   => 12,
// ]);

// 修改用户组别
// $access = $auth->model('access')->modify([
//     'uid'   => 1,
//     'gid'   => 1,
//     'new_gid'   => '12'
// ]);

// 解除组别绑定
// $access = $auth->model('access')->unbind([
//     'uid'   => 1,
//     'gid'   => 2,
// ]);

// 获取用户所在组别
// $access = $auth->model('access')->getUserGroup(2);

// var_dump($access);
// var_dump($auth->model('access')->getError());

// 获取用户权限节点
// $data = Auth::instance()->getAuthIds(1);

// 获取用户权限列表
// $data = Auth::instance()->getAuthList(1);

// 获取用户权限
// $data = Auth::instance()->getRule(1);

// var_dump($data);

// 校验单个权限
// $check = Auth::instance()->check('123asda', 1, true);
// 校验多个权限
// $check = Auth::instance()->check(['123asda', 'aa'], 1, false);

// var_dump($check);

try {
    dd($auth2->check('/admin/sys/auth/group/add', 3));
} catch (DbException $e) {
    dd($e->getMessage());
    dd($e->getConnection()->getConfig());
    // dd($e->getLine());
    // dd($e->getPrevious()->getLine());
}


// dd($auth2->getAuthList(2));

// dd($access);
// dd($auth2->model('access')->getError());

// 获取RBAC模型
// $model = Auth::instance()->model('access');

// var_dump($model);

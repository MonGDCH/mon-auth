# mon-auth

#### 介绍

PHP权限管理类库。

#### 安装使用

1. composer安装本项目

```bash
composer require mongdch/mon-auth
```

2. 如需使用RBAC库，则运行导入database目录下rbac.sql文件到数据库中。按需修改修改增加字段即可。

#### API文档

- 暂未编写，请通过查看examples目录下的demo，阅读了解更多使用方法。

#### Demo

1. JWT

```php

use mon\auth\jwt\Token;
use mon\auth\jwt\Payload;
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

```

2. RBAC

```php

use mon\auth\rbac\Auth;

$config = [
    // 权限开关
    'auth_on'           => true,
    // 用户组数据表名               
    'auth_group'        => 'auth_group',
    // 用户-用户组关系表     
    'auth_group_access' => 'auth_access',
    // 权限规则表    
    'auth_rule'         => 'auth_rule',
    // 超级管理员权限标志       
    'admin_mark'        => '*',
    // 数据库配置              
    'database'          => [
        // 数据库类型
        'type'            => 'mysql',
        // 服务器地址
        'host'            => '127.0.0.1',
        // 数据库名
        'database'        => '',
        // 用户名
        'username'        => '',
        // 密码
        'password'        => '',
        // 端口
        'port'            => '3306',
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 返回结果集类型
        'result_type'     => \PDO::FETCH_ASSOC,
        // 断开自动重连
        'break_reconnect' => false,
    ]
];

Auth::instance()->init($config);
$check = Auth::instance()->check('/admin/sys/auth/group/add', 1);
debug($check);

```

#### 版本

> 1.1.0

* 优化代码，更新依赖
* 增强对Gaia框架支持

> 1.0.11

* 优化注解


> 1.0.10

* 优化代码，优化依赖


> 1.0.9

* 优化代码，优化依赖


> 1.0.8

* 优化整体代码
* 增强RBAC模块，支持通过new的方式，同时运多个权限实例

> 1.0.7

* 优化RBAC模块代码
* 修复修改操作存在的BUG

> 1.0.6

* 补全1.0.5版本发布少的RBAC优化代码

> 1.0.5

* 优化RBAC代码
* 增加RBAC获取内置模型的model方法

> 1.0.4

* 优化代码，中文化JWT类库

> 1.0.3

* 修复自定义权限表名无效BUG

> 1.0.2

* 完善RBAC权限控制
* 发布1.0.2 RC版本

> 1.0.1

* 发布第一个版本
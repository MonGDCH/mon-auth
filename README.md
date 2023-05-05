# mon-auth

#### 介绍

PHP权限管理类库，包含`Jwt`、`RBAC`、`AccessToken`、`Signature`等权限控制类库。


#### 安装使用

1. composer安装本项目

```bash
composer require mongdch/mon-auth
```

2. 如需使用RBAC库，则运行导入database目录下`rbac.sql`文件到数据库中。按需修改修改增加字段即可。

3. 如需使用Mysql版本的`AccessToken`、`Signature`，则运行导入database目录下`api.sql`文件到数据库中，按需修改配置即可


#### API文档

- 暂未编写，请通过查看examples目录下的demo，阅读了解更多使用方法。


#### Demo

1. JWT

```php

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
	dd($jwt);

	// 验证jwt
	$data = $token->check($jwt, $key, $alg);
	dd($data);
}
catch (JwtException $e){
	dd('Msg: '.$e->getMessage(), 'Line: '.$e->getLine(), 'Code: '.$e->getCode());
}

```

```php

use mon\auth\jwt\Auth;

$token = Auth::instance()->create(1, ['pm' => 'tch']);

dd($token);

$data = Auth::instance()->check($token);
dd($data);

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

3. AccessToken

```php

use mon\util\Event;
use mon\auth\api\AccessTokenAuth;
use mon\auth\exception\APIException;

// 初始化
AccessTokenAuth::instance()->init();

$appid = 'abcdefg';
$secret = 'asdas234';

// 自定义验证事件
Event::instance()->listen('access_check', function ($data) {
    // token数据
    // dd($data);

    // 抛出异常 APIException 作为验证不通过的标志
    throw new APIException('自定义验证错误', 0, null, $data);
});



$token = AccessTokenAuth::instance()->create($appid, $secret);

dd($token);

try {
    $decode = AccessTokenAuth::instance()->check($token, $appid, $secret);
    dd($decode);
} catch (APIException $e) {
    dd('验证不通过！' . $e->getMessage() . ' code: ' . $e->getCode());
    // 异常绑定的数据
    dd($e->getData());
}


```

4. apiSignature

```php

use mon\auth\api\SignatureAuth;

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

```


#### 版本

> 1.1.2

* 增加`AccessToken`、`ApiSignature`权限控制
* 重构逻辑代码，优化业务
* 增强对Gaia框架的支持


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
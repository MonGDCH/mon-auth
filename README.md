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

- 暂未编写，请通过查看examples目录下的demo，阅读了解使用方法。


#### 版本

#### 1.0.9

* 优化代码，优化依赖


##### 1.0.8

* 优化整体代码
* 增强RBAC模块，支持通过new的方式，同时运多个权限实例

##### 1.0.7

* 优化RBAC模块代码
* 修复修改操作存在的BUG

##### 1.0.6

* 补全1.0.5版本发布少的RBAC优化代码

##### 1.0.5

* 优化RBAC代码
* 增加RBAC获取内置模型的model方法

##### 1.0.4

* 优化代码，中文化JWT类库

##### 1.0.3

* 修复自定义权限表名无效BUG

##### 1.0.2

* 完善RBAC权限控制
* 发布1.0.2 RC版本

##### 1.0.1

* 发布第一个版本
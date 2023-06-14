<?php

declare(strict_types=1);

namespace mon\auth\rbac;

use mon\orm\Model;
use mon\util\Event;
use mon\util\Instance;
use mon\auth\exception\RbacException;

/**
 * 权限控制
 *
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth = Auth::instance($config);  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（ true 或者 false ）
 *      $auth = Auth::instance($config);  $auth->check([规则1, 规则2], '用户id', false)
 *      第三个参数为 false 时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为 true 时，表示用户值需要具备其中一个条件即可。默认为 true
 * 3，一个用户可以属于多个用户组(think_auth_group_access表 定义了用户所属用户组)。我们需要设置每个用户组拥有哪些规则(think_auth_group 定义了用户组权限)
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.2 优化代码，增加model方法
 * @version 1.0.3 优化代码，增加model容器，支持同一应用new多个Auth实例
 */
class Auth
{
    use Instance;

    /**
     * 初始化标志
     *
     * @var boolean
     */
    protected $init = false;

    /**
     * 缓存的模型实例
     *
     * @var array
     */
    protected $models = [];

    /**
     * 权限DB表默认配置
     *
     * @var array
     */
    protected $config = [
        // 用户组数据表名               
        'auth_group'        => 'auth_group',
        // 用户-用户组关系表     
        'auth_group_access' => 'auth_access',
        // 权限规则表    
        'auth_rule'         => 'auth_rule',
        // 超级管理员权限标志       
        'admin_mark'        => '*',
        // 有效的状态值
        'effective_status'  => 1,
        // 无效的状态值
        'invalid_status'    => 0,
        // 数据库配置              
        'database'          => [
            // 数据库类型，只支持mysql
            'type'          => 'mysql',
            // 服务器地址
            'host'          => '127.0.0.1',
            // 数据库名
            'database'      => '',
            // 用户名
            'username'      => '',
            // 密码
            'password'      => '',
            // 端口
            'port'          => '3306',
            // 数据库连接参数
            'params'        => [],
            // 数据库编码默认采用utf8
            'charset'       => 'utf8mb4',
            // 返回结果集类型
            'result_type'   => \PDO::FETCH_ASSOC,
            // 是否开启读写分离
            'rw_separate'   => false,
            // 查询数据库连接配置，二维数组随机获取节点覆盖默认配置信息
            'read'          => [],
            // 写入数据库连接配置，同上，开启事务后，读取不会调用查询数据库配置
            'write'         => []
        ],
    ];

    /**
     * 构造方法
     *
     * @param array $config 配置信息
     */
    public function __construct(array $config = [])
    {
        if (empty($config)) {
            $this->init($config);
        }
    }

    /**
     * 初始化方法
     *
     * @param array $config 配置信息
     * @return Auth
     */
    public function init(array $config = []): Auth
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
        // 标志初始化
        $this->init = true;
        return $this;
    }

    /**
     * 是否已初始化
     *
     * @return boolean
     */
    public function isInit(): bool
    {
        return $this->init;
    }

    /**
     * 设置配置
     *
     * @param array $config 设置配置信息
     * @return Auth
     */
    public function setConfig(array $config): Auth
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * 获取配置信息
     *
     * @param string $key   配置索引
     * @return mixed
     */
    public function getConfig($key = '')
    {
        if (!empty($key)) {
            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * 校验权限
     *
     * @param  string|array     $name     需要验证的规则列表,支持字符串的单个权限规则或索引数组多个权限规则
     * @param  integer|string   $uid      认证用户的id
     * @param  boolean 		    $relation 如果为 true 表示满足任一条规则即通过验证;如果为 false 则表示需满足所有规则才能通过验证
     * @throws RbacException
     * @return boolean           	  成功返回true，失败返回false
     */
    public function check($name, $uid, bool $relation = true): bool
    {
        // 获取用户需要验证的所有有效规则列表
        $authList = $this->getAuthList($uid);
        if (in_array($this->config['admin_mark'], (array) $authList)) {
            // 触发rack权限验证事件
            $this->triggerEvent($uid, $name, 'admin', $authList, $relation);
            // 具备所有权限
            return true;
        }

        // 获取需求验证的规则
        if (is_string($name)) {
            $name = [strtolower($name)];
        } else if (is_array($name)) {
            $name = array_map('strtolower', $name);
        } else {
            throw new RbacException('不支持的规则类型，只支持string、array类型', RbacException::RBAC_RULE_NOT_SUPPORT);
        }
        // 保存验证通过的规则名
        $list = [];
        // 验证权限
        foreach ($authList as $auth) {
            if (in_array($auth, $name)) {
                $list[] = $auth;
            }
        }
        // 判断验证规则
        if ($relation == true && !empty($list)) {
            // 触发rack权限验证事件
            $this->triggerEvent($uid, $name, 'check', $list, $relation);
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation == false && empty($diff)) {
            // 触发rack权限验证事件
            $this->triggerEvent($uid, $name, 'diff', $diff, $relation);
            return true;
        }

        return false;
    }

    /**
     * 获取角色权限节点对应权限
     *
     * @param  integer|string $uid 用户ID
     * @return array
     */
    public function getAuthIds($uid): array
    {
        // 获取规则节点
        $ids = [];
        $groups = $this->model('Access')->getUserGroup($uid);
        foreach ($groups as $v) {
            $ids = array_merge($ids, explode(',', trim($v['rules'], ',')));
        }

        return array_unique($ids);
    }

    /**
     * 获取用户权限规则列表
     *
     * @param  integer|string $uid 用户ID
     * @return array
     */
    public function getAuthList($uid): array
    {
        // 获取规则节点
        $ids = $this->getAuthIds($uid);
        if (empty($ids)) {
            return [];
        }
        $authList = [];
        // 判断是否拥有所有权限
        if (in_array($this->config['admin_mark'], (array) $ids)) {
            $authList[] = $this->config['admin_mark'];
            return $authList;
        }
        // 获取权限规则
        $rules = $this->getRule($uid);
        foreach ($rules as $rule) {
            $authList[] = strtolower($rule['name']);
        }

        return array_unique($authList);
    }

    /**
     * 获取权限规则
     *
     * @param integer|string $uid  用户ID
     * @return array
     */
    public function getRule($uid): array
    {
        // 获取规则节点
        $ids = $this->getAuthIds($uid);
        if (empty($ids)) {
            return [];
        }
        // 构造查询条件
        $map['status'] = 1;
        if (!in_array($this->config['admin_mark'], (array) $ids)) {
            $map['id'] = ['in', $ids];
        }
        // 获取权限规则
        $rules = $this->model('Rule')->where($map)->field('id, pid, name, title')->select();
        return $rules;
    }

    /**
     * 获取模型
     *
     * @param string $name  名称
     * @param boolean $cache    是否从缓存中获取
     * @return \mon\orm\Model
     */
    public function model(string $name, bool $cache = true): Model
    {
        if (!in_array(strtolower($name), ['access', 'group', 'rule'])) {
            throw new RbacException('不存在对应RBAC权限模型', RbacException::RBAC_MODEL_NOT_FOUND);
        }

        // 获取实例
        if ($cache && isset($this->models[$name])) {
            return $this->models[$name];
        }

        $class = '\\mon\\auth\\rbac\\model\\' . ucwords($name);
        $this->models[$name] = new $class($this);
        return $this->models[$name];
    }

    /**
     * 触发验证事件
     *
     * @param string|integer $uid   用户ID
     * @param string|array $name  验证规则名称
     * @param string $type  验证事件类型
     * @param array $auth   权限列表
     * @param boolean $relation 是否需要满足全部规则通过才通过
     * @return void
     */
    protected function triggerEvent($uid, $name, string $type, array $auth,  bool $relation)
    {
        Event::instance()->trigger('rbac_check', [
            'type' => $type,
            'auth' => $auth,
            'uid'  => $uid,
            'name' => $name,
            'relation' => $relation
        ]);
    }
}

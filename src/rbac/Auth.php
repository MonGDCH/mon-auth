<?php

namespace mon\auth\rbac;

use mon\auth\rbac\model\Access;
use mon\auth\rbac\model\Rule;
use mon\util\Instance;

/**
 * 权限控制
 *
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth = Auth::instance($config);  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（ true 或者 false ）
 *      $auth = Auth::instance($config);  $auth->check('规则1,规则2','用户id', false)
 *      第三个参数为 false 时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为 true 时，表示用户值需要具备其中一个条件即可。默认为 true
 * 3，一个用户可以属于多个用户组(think_auth_group_access表 定义了用户所属用户组)。我们需要设置每个用户组拥有哪些规则(think_auth_group 定义了用户组权限)
 * 
 * @version 1.0.1
 * @author Mon <985558837@qq.com>
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
     * 权限DB表默认配置
     *
     * @var array
     */
    protected $config = [
        'auth_on'           => true,                // 权限开关
        'auth_group'        => 'auth_group',        // 用户组数据表名
        'auth_group_access' => 'auth_access',       // 用户-用户组关系表
        'auth_rule'         => 'auth_rule',         // 权限规则表
        'admin_mark'        => '*',                 // 超级管理员权限标志
        'database'          => [                    // 数据库配置
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
            'port'            => '',
            // 数据库连接参数
            'params'          => [],
            // 数据库编码默认采用utf8
            'charset'         => 'utf8',
            // 返回结果集类型
            'result_type'     => PDO::FETCH_ASSOC,
        ],
    ];

    /**
     * 构造方法
     */
    protected function init(array $config)
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
    public function isInit()
    {
        return $this->init;
    }

    /**
     * 设置配置
     *
     * @param array   $config   设置配置信息
     * @param boolean $setDb    重置mysql链接配置
     * @return void
     */
    public function setConfig(array $config, $setDb = false)
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * 获取配置信息
     *
     * @param string $key   配置索引
     * @return void
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
     * @param  string|array $name     需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param  integer 		$uid      认证用户的id
     * @param  boolean 		$relation 如果为 true 表示满足任一条规则即通过验证;如果为 false 则表示需满足所有规则才能通过验证
     * @return boolean           	  成功返回true，失败返回false
     */
    public function check($name, $uid, $relation = true)
    {
        if (!$this->config['auth_on']) {
            return true;
        }
        // 获取用户需要验证的所有有效规则列表
        $authList = $this->getAuthList($uid);
        if (in_array($this->config['admin_mark'], (array) $authList)) {
            // 具备所有权限
            return true;
        }

        // 获取需求验证的规则
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
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
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation == false && empty($diff)) {
            return true;
        }

        return false;
    }

    /**
     * 根据用户所属组别
     *
     * @param  [type] $uid 用户ID
     * @return [type]      [description]
     */
    public function getGroups($uid)
    {
        // 判断缓存
        static $groups = [];
        if (isset($groups[$uid])) {
            return $groups[$uid];
        }
        // 查询获取用户组别信息
        $userGroups = Access::instance()->getUserGroup($uid);

        $groups[$uid] = $userGroups ?: [];
        return $groups[$uid];
    }

    /**
     * 获取角色权限节点对应权限
     *
     * @param  [type] $uid 用户ID
     * @return [type]      [description]
     */
    public function getAuthIds($uid)
    {
        static $authIds = [];
        if (isset($authIds[$uid])) {
            return $authIds[$uid];
        }
        // 获取规则节点
        $ids = [];
        $groups = $this->getGroups($uid);
        foreach ($groups as $v) {
            $ids = array_merge($ids, explode(',', trim($v['rules'], ',')));
        }
        $authIds[$uid] = array_unique($ids);
        return $authIds[$uid];
    }

    /**
     * 获取用户权限列表
     *
     * @param  [type] $uid 用户ID
     * @return [type]      [description]
     */
    public function getAuthList($uid)
    {
        static $auths = [];
        if (isset($auths[$uid])) {
            return $auths[$uid];
        }
        // 获取规则节点
        $ids = $this->getAuthIds($uid);
        if (empty($ids)) {
            $auths[$uid] = [];
            return [];
        }
        $authList = [];
        // 判断是否拥有所有权限
        if (in_array($this->config['admin_mark'], (array) $ids)) {
            $authList[] = $this->config['admin_mark'];
            $auths[$uid] = $authList;
            return $auths[$uid];
        }
        // 获取权限规则
        $rules = $this->getRule($uid);
        foreach ($rules as $rule) {
            $authList[] = strtolower($rule['name']);
        }
        $auths[$uid] = array_unique($authList);

        return $auths[$uid];
    }

    /**
     * 获取权限规则
     *
     * @param [type] $uid
     * @return void
     */
    public function getRule($uid)
    {
        static $rules = [];
        if (isset($rules[$uid])) {
            return $rules[$uid];
        }
        // 获取规则节点
        $ids = $this->getAuthIds($uid);
        if (empty($ids)) {
            $rules[$uid] = [];
            return [];
        }
        // 构造查询条件
        $map['status'] = 1;
        if (!in_array($this->config['admin_mark'], (array) $ids)) {
            $map['id'] = ['in', $ids];
        }
        // 获取权限规则
        $rules[$uid] = Rule::instance()->where($map)->field('id, pid, name, title')->select();
        return $rules[$uid];
    }
}

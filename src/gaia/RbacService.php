<?php

declare(strict_types=1);

namespace support\auth;

use mon\env\Config;
use mon\util\Instance;
use mon\auth\rbac\Auth;
use mon\auth\exception\RbacException;

/**
 * RBAC权限控制服务
 * 
 * @method array getAuthIds(integer|string $uid) 获取角色权限节点对应权限
 * @method array getAuthList(integer|string $uid) 获取用户权限规则列表
 * @method array getRule(integer|string $uid) 获取权限规则
 * @method mixed model(string $name, boolean $cache = true) 获取权限模型
 *
 * @author Mon <985558837@qq.com>
 * @version 1.0.1 优化注解  2022-07-15
 */
class RbacService
{
    use Instance;

    /**
     * 缓存服务对象
     *
     * @var Auth
     */
    protected $service;

    /**
     * 错误信息
     *
     * @var string
     */
    protected $error = '';

    /**
     * 错误码
     *
     * @var integer
     */
    protected $errorCode = 0;

    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [
        // 权限开关
        'enable'            => true,
        // 用户组数据表名     
        'auth_group'        => 'auth_group',
        // 用户-用户组关系表
        'auth_group_access' => 'auth_access',
        // 权限规则表
        'auth_rule'         => 'auth_rule',
        // 超级管理员权限标志
        'admin_mark'        => '*',
        // 数据库配置              
        'database'          => 'default'
    ];

    /**
     * 构造方法
     */
    public function __construct()
    {
        $config = Config::instance()->get('auth.rbac', []);
        $this->register($config);
    }

    /**
     * 注册配置信息
     *
     * @param array $config
     * @return RbacService
     */
    public function register(array $config): RbacService
    {
        $this->config = array_merge($this->config, $config);
        if (is_string($this->config['database'])) {
            $dbconfig = Config::instance()->get('database.' . $this->config['database'], []);
            $this->config['database'] = $dbconfig;
        }

        return $this;
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError(): string
    {
        $error = $this->error;
        $this->error = '';
        return $error;
    }

    /**
     * 获取错误码
     *
     * @return integer
     */
    public function getErrorCode(): int
    {
        $code = $this->errorCode;
        $this->errorCode = 0;
        return $code;
    }


    /**
     * 校验权限，重载优化Auth类的check方法
     *
     * @param  string|array     $name     需要验证的规则列表,支持字符串的单个权限规则或索引数组多个权限规则
     * @param  integer|string   $uid      认证用户的id
     * @param  boolean 		    $relation 如果为 true 表示满足任一条规则即通过验证;如果为 false 则表示需满足所有规则才能通过验证
     * @throws RbacException
     * @return boolean           	  成功返回true，失败返回false
     */
    public function check($name, $uid, bool $relation = true): bool
    {
        try {
            $check = $this->getService()->check($name, $uid, $relation);
            if (!$check) {
                $this->error = '暂无权限';
                $this->error = -1;
                return false;
            }

            return true;
        } catch (RbacException $e) {
            $this->error = $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    /**
     * 获取配置信息
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 获取缓存服务实例
     *
     * @return Auth
     */
    public function getService(): Auth
    {
        if (is_null($this->service)) {
            $this->service = Auth::instance()->init($this->config);
        }

        return $this->service;
    }

    /**
     * 回调服务
     *
     * @param string $name      方法名
     * @param mixed $arguments 参数列表
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getService(), $name], (array) $arguments);
    }
}

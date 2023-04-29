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
 * @version 1.0.0
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
     * 私有构造方法
     */
    protected function __construct()
    {
        $config = Config::instance()->get('auth.rbac', []);
        $this->service = (new Auth)->init($this->parseConfig($config));
    }

    /**
     * 获取权限服务
     *
     * @return Auth
     */
    public function getService(): Auth
    {
        return $this->service;
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
     * 注册配置信息
     *
     * @param array $config 配置信息
     * @return RbacService
     */
    public function register(array $config): RbacService
    {
        $config = $this->parseConfig($config);
        $this->getService()->init($config);
        return $this;
    }

    /**
     * 校验权限，重载优化Auth类的check方法
     *
     * @param  string|array     $name     需要验证的规则列表,支持字符串的单个权限规则或索引数组多个权限规则
     * @param  integer|string   $uid      认证用户的id
     * @param  boolean 		    $relation 如果为 true 表示满足任一条规则即通过验证;如果为 false 则表示需满足所有规则才能通过验证
     * @return boolean           	  成功返回true，失败返回false
     */
    public function check($name, $uid, bool $relation = true): bool
    {
        try {
            $check = $this->getService()->check($name, $uid, $relation);
            if (!$check) {
                $this->error = '暂无权限';
                $this->errorCode = 0;
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
     * 回调服务
     *
     * @param string $name      方法名
     * @param mixed $arguments 参数列表
     * @return mixed
     */
    public function __call(string $name, $arguments)
    {
        return call_user_func_array([$this->getService(), $name], (array) $arguments);
    }

    /**
     * 解析完善配置信息
     *
     * @param array $config 配置信息
     * @return array
     */
    protected function parseConfig(array $config): array
    {
        if (is_string($config['database'])) {
            $dbconfig = Config::instance()->get('database.' . $config['database'], []);
            $config['database'] = $dbconfig;
        }

        return $config;
    }
}

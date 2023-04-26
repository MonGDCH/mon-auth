<?php

declare(strict_types=1);

namespace mon\auth\api;

use mon\util\Instance;
use mon\auth\api\dao\ArrayDao;
use mon\auth\api\dao\DatabaseDao;
use mon\auth\api\dao\DaoInterface;
use mon\auth\api\driver\AccessToken;
use mon\auth\exception\APIException;

/**
 * AccessToken签名API权限控制
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class AccessTokenAuth
{
    use Instance;

    /**
     * AccessToken初始化标志
     *
     * @var boolean
     */
    protected $init = false;

    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [
        // 权限开关
        'enable'    => true,
        // 字段映射
        'field'     => [
            // app_id字段名
            'app_id'    => 'app_id',
            // 有效时间字段名
            'expire'    => 'expire',
            // 签发的IP
            'ip'        => 'ip',
        ],
        // 有效时间，单位秒
        'expire'    => 3600,
        // 默认加密盐
        'salt'      => 'a!khg#-$%iu_ow1.08',
        // 数据源配置
        'dao'       => 'array',
        // Dao类型为 array 的配置
        'array'     => [
            // APP数据列表
            'data'      => [
                [
                    'app_id'    => 'TEST123456789',
                    'secret'    => 'klasjhghaalskfjqwpetoijhxc',
                    'name'      => '测试',
                    'status'    => 1,
                    'expired_time'  => 1234567890,
                ]

            ],
        ],
        // Dao类型为 db 的配置
        'db'        => [
            // 操作表，
            'table'     => 'api_sign',
            // 数据库配置
            'config'    => [
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
        ]

    ];

    /**
     * AccessToken实例
     *
     * @var AccessToken
     */
    protected $driver;

    /**
     * Dao实例
     *
     * @var DaoInterface
     */
    protected $dao;

    /**
     * 初始化AccessToken
     *
     * @param array $config 配置信息
     * @return AccessTokenAuth
     */
    public function init(array $config = []): AccessTokenAuth
    {
        // 定义配置
        $this->config = array_merge($this->config, $config);
        // 获取AccessToken实例
        $this->driver = new AccessToken($this->config['salt'], $this->config['field']);
        // 初始化数据驱动

        switch ($this->config['dao']) {
            case 'array':
                // 静态数组数据
                $this->dao = new ArrayDao($this->config['array']['data']);
                break;
            case 'db':
                $this->dao = new DatabaseDao($this->config['db']['table'], $this->config['db']['config']);
                break;
            default:
                throw new APIException('Dao类型不支持', APIException::DAO_NOT_SUPPORT);
                break;
        }

        // 记录标志
        $this->init = true;

        return $this;
    }

    /**
     * 是否初始化AccessToken
     *
     * @return boolean
     */
    public function isInit(): bool
    {
        return $this->init;
    }

    /**
     * 获取AccessToken配置
     *
     * @return array
     */
    public function getConfig(string $field = ''): array
    {
        if (!empty($field)) {
            return $this->config[$field];
        }
        return $this->config;
    }

    /**
     * 获取AccessToken实例
     *
     * @return AccessToken
     */
    public function getDriver(): AccessToken
    {
        return $this->driver;
    }

    /**
     * 获取Dao实例
     *
     * @return DaoInterface
     */
    public function getDao(): DaoInterface
    {
        return $this->dao;
    }

    /**
     * 创建AccessToken
     *
     * @param string $app_id    应用ID
     * @param string $secret    应用秘钥
     * @param array $extend     扩展数据
     * @throws APIException
     * @return string
     */
    public function create(string $app_id, string $secret, array $extend = []): string
    {
        if (!$this->isInit()) {
            throw new APIException('未初始化权限控制', APIException::AUTH_NOT_INIT);
        }

        return $this->getDriver()->create($app_id, $secret, $extend, $this->getConfig('expire'));
    }

    /**
     * 结合Dao数据创建AccessToken
     *
     * @param string $app_id    应用ID
     * @param array $extend     扩展数据
     * @throws APIException
     * @return string
     */
    public function createToken(string $app_id, array $extend = []): string
    {
        if (!$this->isInit()) {
            throw new APIException('未初始化权限控制', APIException::AUTH_NOT_INIT);
        }

        // 获取应用信息
        $info = $this->getDao()->getInfo($app_id);
        if (!$info) {
            throw new APIException('APPID不存在', APIException::APPID_NOT_FOUND);
        }
        if ($info['status'] != 1) {
            throw new APIException('APPID无效', APIException::APPID_STATUS_ERROR);
        }
        if ($info['expired_time'] != 0 && $info['expired_time'] < time()) {
            throw new APIException('APPID已过期', APIException::APPID_TIME_INVALID);
        }

        return $this->getDriver()->create($app_id, $info['secret'], $extend, $this->getConfig('expire'));
    }
}

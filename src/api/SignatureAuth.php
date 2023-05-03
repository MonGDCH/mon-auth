<?php

declare(strict_types=1);

namespace mon\auth\api;

use mon\util\Instance;
use mon\auth\api\driver\Signature;
use mon\auth\exception\APIException;

/**
 * API签名权限控制
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class SignatureAuth extends ApiAuth implements ApiAuthInterface
{
    use Instance;

    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [
        // 默认加密盐
        'salt'  => 'df2!)*&+sdfg_687#@',
        // 字段映射
        'field' => [
            // app_id字段名
            'app_id'    => 'app_id',
            // 签名字段名
            'signature' => 'signature',
            // 签名时间字段名
            'timestamp' => 'timestamp',
            // 随机字符串字段名
            'noncestr'  => 'noncestr',
            // secret key名
            'secret'    => 'key'
        ],
        // 有效时间，单位秒
        'expire'    => 3600,
        // 数据源配置
        'dao'      => [
            // 驱动，默认数组驱动
            'driver'    => \mon\auth\api\dao\ArrayDao::class,
            // 构造方法传入参数
            'construct'    => [
                // 数组驱动APP应用数据列表，驱动为 ArrayDao 时有效
                'data'  => [
                    // [
                    //     // 应用ID
                    //     'app_id'    => 'TEST123456789',
                    //     // 应用秘钥
                    //     'secret'    => 'klasjhghaalskfjqwpetoijhxc',
                    //     // 应用名称
                    //     'name'      => '测试',
                    //     // 应用状态，1有效 0无效
                    //     'status'    => 1,
                    //     // 应用过期时间戳
                    //     'expired_time'  => 0,
                    // ]
                ],
                // 数据库驱动操作表，驱动为 DatabaseDao 时有效
                'table'     => 'api_sign',
                // 数据库链接配置，驱动为 DatabaseDao 时有效
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
                ]
            ]
        ]
    ];

    /**
     * 获取驱动实例
     *
     * @return Signature
     */
    public function getDriver(): Signature
    {
        return $this->driver;
    }

    /**
     * 创建签名请求数据
     *
     * @param string $app_id    应用ID
     * @param string $secret    应用秘钥
     * @param array $data       需要签名的数据
     * @return array
     */
    public function create(string $app_id, string $secret, array $data = []): array
    {
        if (!$this->isInit()) {
            throw new APIException('未初始化权限控制', APIException::AUTH_INIT_ERROR);
        }

        return $this->getDriver()->create($app_id, $secret, $data);
    }

    /**
     * 结合Dao数据创建API签名
     *
     * @param string $app_id    应用ID
     * @param array $data       需要签名的数据
     * @throws APIException
     * @return array
     */
    public function createToken(string $app_id, array $data = []): array
    {
        if (!$this->isInit()) {
            throw new APIException('未初始化权限控制', APIException::AUTH_INIT_ERROR);
        }
        // 获取应用信息
        $info = $this->getAppInfo($app_id);

        // 创建签名
        return $this->create($app_id, $info['secret'], $data);
    }

    /**
     * 验证签名
     *
     * @param string $secret    应用秘钥
     * @param array $data       签名数据
     * @return boolean
     */
    public function check(string $secret, array $data): bool
    {
        if (!$this->isInit()) {
            throw new APIException('未初始化权限控制', APIException::AUTH_INIT_ERROR);
        }

        // 验证签名
        $this->getDriver()->check($secret, $data);

        // 验证签名有效期
        $field = $this->getConfig('field');
        if (time() > ($data[$field['timestamp']] + $this->getConfig('expire'))) {
            throw new APIException('API签名已过期', APIException::SIGN_TIME_INVALID);
        }

        return true;
    }

    /**
     * 验证签名
     *
     * @param array $data   签名数据
     * @return boolean
     */
    public function checkToken(array $data): bool
    {
        if (!$this->isInit()) {
            throw new APIException('未初始化权限控制', APIException::AUTH_INIT_ERROR);
        }
        $field = $this->getConfig('field');
        $app_id = $data[$field['app_id']] ?? null;
        if (empty($app_id)) {
            throw new APIException('无效签名', APIException::APPID_PARAMS_FAILD);
        }

        // 获取应用信息
        $info = $this->getAppInfo($app_id);
        // 验证签名
        return $this->check($info['secret'], $data);
    }

    /**
     * 初始化API驱动
     *
     * @return void
     */
    protected function initDriver()
    {
        // 获取AccessToken实例
        $this->driver = new Signature($this->getConfig('salt'), $this->getConfig('field'));
    }
}

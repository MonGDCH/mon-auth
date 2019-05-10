<?php
namespace mon\auth;

use mon\env\Config;
use mon\auth\exception\JwtException;

/**
 * 类JWT权限控制
 *
 * @version 1.0.0
 */
class Jwt
{
    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [
        'salt'  => 'mon',
        'life'  => 3600,
        'algs'  => 'HS256'
    ];

    /**
     * 支持的加密方式
     *
     * @var [type]
     */
    protected $algs = [
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS512' => ['hash_hmac', 'SHA512'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'RS256' => ['openssl', 'SHA256'],
        'RS384' => ['openssl', 'SHA384'],
        'RS512' => ['openssl', 'SHA512'],
    ];

    /**
     * 构造方法
     *
     * @param array $config [description]
     */
    public function __construct(array $config = [])
    {
        $this->config = empty($config) ? Config::instance()->get('mon_auth.jwt', []) : $config;
        if(empty($this->config)){
            throw new JwtException('configuration information is not set!', 1);
        }
    }

    /**
     * 获取配置信息
     *
     * @return [type] [description]
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 签名
     *
     * @param  string $payload     JSON化的payload
     * @param  string $key         签名盐
     * @param  int    $create_time 创建时间
     * @return [type]              [description]
     */
    public function sign(array $payload, string $key)
    {
        $create_time = $payload['create_time'];
        return md5($payload . $key . $create_time);
    }

    /**
     * 验证签名
     *
     * @param  string $sign    [description]
     * @param  array  $payload [description]
     * @return [type]          [description]
     */
    public function checkSign(string $sign, array $payload)
    {

    }

    /**
     * 加密
     *
     * @param  string $info JSON信息
     * @param  string $key  加密盐
     * @param  string $alg  加密方式
     * @return [type]       [description]
     */
    public function encode(string $info, string $key, string $alg = 'HS256')
    {

    }
}
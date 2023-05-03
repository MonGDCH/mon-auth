<?php

declare(strict_types=1);

namespace mon\auth\api\driver;

use mon\util\Event;
use mon\util\Common;
use mon\util\Instance;
use mon\auth\exception\APIException;

/**
 * 接口 Signature 签名验证
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class Signature implements DriverInterface
{
    use Instance;

    /**
     * 内置的加密盐
     *
     * @var string
     */
    protected $encrypt_salt = 'df2!)*&+sdfg_687#@';

    /**
     * 字段名映射
     *
     * @var array
     */
    protected $field_map = [
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
    ];

    /**
     * 构造方法
     *
     * @param array $field_map  字段名映射
     */
    public function __construct(string $salt = 'df2!)*&+sdfg_687#@', array $field_map = [])
    {
        $this->encrypt_salt = $salt;
        $this->field_map = array_merge($this->field_map, $field_map);
    }

    /**
     * 获取加密秘钥
     *
     * @param string $secret
     * @return string
     */
    public function getSecret(string $secret): string
    {
        return $this->encrypt_salt . $secret;
    }

    /**
     * 获取映射的字段值
     *
     * @param string $key   映射名
     * @return string   字段值
     */
    public function getField(string $key): string
    {
        return $this->field_map[$key];
    }

    /**
     * 设置字段映射
     *
     * @param array $field  映射的规则
     * @return Signature
     */
    public function setField(array $field): Signature
    {
        $this->field_map = array_merge($this->field_map, $field);
        return $this;
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
        $signData = $this->getSignData($app_id, $data);
        $signData[$this->getField('signature')] = $this->getSign($signData, $secret);
        return $signData;
    }

    /**
     * 验证请求参数签名
     *
     * @param string $secret    应用秘钥
     * @param array $data       请求参数
     * @return void
     */
    public function check(string $secret, array $data): bool
    {
        $sign = $data[$this->getField('signature')] ?? '';
        if (empty($sign)) {
            throw new APIException('API签名不存在', APIException::SIGN_NOT_FOUND);
        }
        unset($data[$this->getField('signature')]);
        if ($sign != $this->getSign($data, $secret)) {
            throw new APIException('API签名错误', APIException::SIGN_VERIFY_FAIL);
        }

        // 触发AccessToken验证事件，回调方法可通过 throw APIException 增加自定义的验证方式
        Event::instance()->trigger('sign_check', $data);

        return true;
    }

    /**
     * 获取用来生成签名的数据
     *
     * @param string $app_id    app_id
     * @param array $data       签名的原数据
     * @return array
     */
    public function getSignData(string $app_id, array $data = []): array
    {
        $data[$this->getField('app_id')] = $app_id;
        // 创建时间，用于验证有效期
        $data[$this->getField('timestamp')] = time();
        // 随机字符串，用于保障随机性
        $data[$this->getField('noncestr')] = Common::instance()->randString(32);
        return $data;
    }

    /**
     * 生成签名
     *
     * @param array $data   生成签名用的数据
     * @param string $secret   签名最后的key值
     * @param string $key   签名的key名
     * @return string
     */
    public function getSign(array $data, string $secret, string $key = ''): string
    {
        // 获取key名
        $key = $key ?: $this->getField('secret');
        // 签名步骤一：按字典序排序数组参数
        ksort($data);
        $string = http_build_query($data);
        // 签名步骤二：在string后加入KEY
        $string = $string . "&{$key}=" . $this->getSecret($secret);
        // 签名步骤三：md5加密
        $string = md5($string);
        // 签名步骤四：所有字符转为大写
        return strtoupper($string);
    }
}

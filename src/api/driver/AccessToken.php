<?php

declare(strict_types=1);

namespace mon\auth\api\driver;

use mon\util\Tool;
use mon\util\Event;
use mon\util\Nbed64;
use mon\util\Instance;
use mon\auth\exception\ApiException;

/**
 * 接口 AccessToken 权限控制
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class AccessToken implements DriverInterface
{
    use Instance;

    /**
     * 内置的加密盐
     *
     * @var string
     */
    protected $encrypt_salt = 'a!khg#-$%iu_ow1.08';

    /**
     * 字段名映射
     *
     * @var array
     */
    protected $field_map = [
        // app_id字段名
        'app_id'    => 'app_id',
        // 有效时间字段名
        'expire'    => 'expire',
        // 签发的IP
        'ip'        => 'ip'
    ];

    /**
     * 构造方法
     *
     * @param string $salt
     * @param array $field_map
     */
    public function __construct(string $salt = 'a!khg#-$%iu_ow1.08', array $field_map = [])
    {
        $this->encrypt_salt = $salt;
        $this->field_map = array_merge($this->field_map, $field_map);
    }

    /**
     * 获取内置加密盐
     *
     * @return string
     */
    public function getSalt(): string
    {
        return $this->encrypt_salt;
    }

    /**
     * 设置内置加密盐
     *
     * @param string $salt  加密盐
     * @return AccessToken
     */
    public function setSalt(string $salt): AccessToken
    {
        $this->encrypt_salt = $salt;
        return $this;
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
     * @return AccessToken
     */
    public function setField(array $field): AccessToken
    {
        $this->field_map = array_merge($this->field_map, $field);
        return $this;
    }

    /**
     * 创建AccessToken
     *
     * @param string $app_id    应用ID
     * @param string $secret    应用秘钥
     * @param array $extend     扩展的数据
     * @param integer $exp      有效时间
     * @return string   生成的AccessToken
     */
    public function create(string $app_id, string $secret, array $extend = [], int $exp = 7200): string
    {
        // 过期时间
        $expire_time = $this->getExpireTime($exp);
        // 盐
        $encrypt_salt = $this->getEncryptSalt($secret);
        // token数据
        $data = array_merge($extend, [
            $this->getField('app_id') => $app_id,
            $this->getField('expire') => $expire_time,
            $this->getField('ip') => Tool::instance()->ip()
        ]);
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        return Nbed64::instance()->stringEncrypt($json, $encrypt_salt);
    }

    /**
     * 解析AccessToken数据
     *
     * @param string $token token
     * @param string $salt  应用秘钥
     * @throws ApiException
     * @return array    token数据
     */
    public function parse(string $token, string $secret): array
    {
        // 盐
        $encrypt_salt = $this->getEncryptSalt($secret);
        // 解析token数据
        $json = Nbed64::instance()->stringDecrypt($token, $encrypt_salt);
        // 修正解密后输出的JON格式错误：存在零宽的控制符(Control character error)
        $json = preg_replace('/[[:cntrl:]]/mu', '', $json);
        if (empty($json)) {
            throw new ApiException('无效的AccessToken!', ApiException::ACCESS_TOKEN_ERROR);
        }
        $data = json_decode($json, true);
        if (json_last_error()) {
            throw new ApiException('无效的AccessToken! ' . json_last_error_msg(), ApiException::ACCESS_TOKEN_ERROR);
        }

        return $data;
    }

    /**
     * 验证AccessToken
     *
     * @param string $token token
     * @param string $app_id  应用ID
     * @param string $secret  应用秘钥
     * @throws ApiException
     * @return array    token数据
     */
    public function check(string $token, string $app_id, string $secret): array
    {
        // 获取token数据
        $data = $this->parse($token, $secret);
        // 校验APP_id
        if ($app_id != $data[$this->getField('app_id')]) {
            throw new ApiException('AppID不匹配', ApiException::ACCESS_TOKEN_FAILD);
        }
        // 校验有效期
        if (time() > $data[$this->getField('expire')]) {
            throw new ApiException('Token已过期', ApiException::ACCESS_TOKEN_INVALID);
        }

        // 触发AccessToken验证事件，回调方法可通过 throw APIException 增加自定义的验证方式
        Event::instance()->trigger('access_check', $data);

        return $data;
    }

    /**
     * 获取过期时间
     *
     * @param integer $exp
     * @return integer
     */
    protected function getExpireTime(int $exp): int
    {
        return time() + $exp;
    }

    /**
     * 获取加密盐
     *
     * @param string $salt  盐
     * @return string
     */
    protected function getEncryptSalt(string $salt): string
    {
        return $this->getSalt() . $salt;
    }
}

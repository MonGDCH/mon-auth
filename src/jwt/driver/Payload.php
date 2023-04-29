<?php

declare(strict_types=1);

namespace mon\auth\jwt\driver;

use ArrayAccess;
use mon\auth\exception\JwtException;

/**
 * JWT payload 数据
 *
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class Payload implements ArrayAccess
{
    /**
     * JWT生存时间
     *
     * @var integer
     */
    protected $exp = 0;

    /**
     * JWT启用时间
     *
     * @var integer
     */
    protected $nbf = 0;

    /**
     * 数据集
     *
     * @var array
     */
    protected $data = [];

    /**
     * 设置iss, 签发者
     *
     * @param  mixed $issuer 签发者
     * @return Payload
     */
    public function setIss($issuer): Payload
    {
        $this->data['iss'] = $issuer;
        return $this;
    }

    /**
     * 获取iss
     *
     * @return mixed
     */
    public function getIss()
    {
        return isset($this->data['iss']) ? $this->data['iss'] : '';
    }

    /**
     * 设置sub, 所面向的用户
     *
     * @param  mixed $issuer 所面向的用户
     * @return Payload
     */
    public function setSub($sub): Payload
    {
        $this->data['sub'] = $sub;
        return $this;
    }

    /**
     * 获取sub
     *
     * @return mixed
     */
    public function getSub()
    {
        return isset($this->data['sub']) ? $this->data['sub'] : '';
    }

    /**
     * 设置aud, 接受者
     *
     * @param mixed $aud 接受者
     * @return Payload
     */
    public function setAud($aud): Payload
    {
        $this->data['aud'] = $aud;
        return $this;
    }

    /**
     * 获取Aud
     *
     * @return mixed
     */
    public function getAud()
    {
        return isset($this->data['aud']) ? $this->data['aud'] : '';
    }

    /**
     * 设置jti, web-token提供唯一标识
     *
     * @param mixed $jti web-token提供唯一标识
     * @return Payload
     */
    public function setJti($jti): Payload
    {
        $this->data['jti'] = $jti;
        return $this;
    }

    /**
     * 获取jti
     *
     * @return mixed
     */
    public function getJti()
    {
        return isset($this->data['jti']) ? $this->data['jti'] : '';
    }

    /**
     * 设置ext, 扩展数据
     *
     * @param array $ext 扩展数据
     * @return Payload
     */
    public function setExt(array $ext): Payload
    {
        $this->data['ext'] = $ext;
        return $this;
    }

    /**
     * 获取ext
     *
     * @return array
     */
    public function getExt(): array
    {
        return isset($this->data['ext']) ? $this->data['ext'] : [];
    }

    /**
     * 设置exp, 有效时间
     *
     * @param integer $exp 有效时间
     * @return Payload
     */
    public function setExp(int $exp): Payload
    {
        $this->exp = $exp;
        return $this;
    }

    /**
     * 获取exp
     *
     * @return integer
     */
    public function getExp(): int
    {
        return $this->exp;
    }

    /**
     * 设置nbf, 多少秒后生效
     *
     * @param integer $nbf 多少秒后生效
     * @return Payload
     */
    public function setNbf(int $nbf): Payload
    {
        $this->nbf = $nbf;
        return $this;
    }

    /**
     * 获取nbf
     *
     * @return integer
     */
    public function getNbf(): int
    {
        return $this->nbf;
    }

    /**
     * 获取payload数据
     *
     * @throws JwtException
     * @return array
     */
    public function getData(): array
    {
        if (empty($this->data)) {
            throw new JwtException('payload is empty', JwtException::JWT_PAYLOAD_NOT_EMPTY);
        }
        $payload = $this->data;
        $payload['iat'] = time();
        // 设置启用时间
        if ($this->nbf > 0) {
            $payload['nbf'] = $payload['iat'] + $this->nbf;
        }
        // 设置有效时间
        if ($this->exp > 0) {
            $payload['exp'] = isset($payload['nbf']) ? ($payload['nbf'] + $this->exp) : ($payload['iat'] + $this->exp);
        }

        return $payload;
    }

    /**
     * 设置data值
     *
     * @param string 		$key   字段名
     * @param string|array  $value 值
     * @return void
     */
    public function __set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * 获取data值
     *
     * @param  string $key  key值
     * @return mixed
     */
    public function __get(string $key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * isset
     *
     * @param  string $offset  key值
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * get
     *
     * @param  string $offset  key值
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * set
     *
     * @param  string $offset key值
     * @param  mixed $value value值
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * unset
     *
     * @param  string $offset key值
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}

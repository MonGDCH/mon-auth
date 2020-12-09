<?php

namespace mon\auth\jwt;

use ArrayAccess;
use mon\auth\exception\JwtException;

/**
 * JWT payload 数据
 *
 * @author Mon <985558837@qq.com>
 * @version v1.0.1
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
     * @param  string $issuer 签发者
     * @return Payload
     */
    public function setIss($issuer)
    {
        $this->data['iss'] = $issuer;
        return $this;
    }

    /**
     * 获取iss
     *
     * @return string
     */
    public function getIss()
    {
        return isset($this->data['iss']) ? $this->data['iss'] : null;
    }

    /**
     * 设置sub, 所面向的用户
     *
     * @param  string $issuer 所面向的用户
     * @return Payload
     */
    public function setSub($sub)
    {
        $this->data['sub'] = $sub;
        return $this;
    }

    /**
     * 获取sub
     *
     * @return string
     */
    public function getSub()
    {
        return isset($this->data['sub']) ? $this->data['sub'] : null;
    }

    /**
     * 设置aud, 接受者
     *
     * @param string $aud 接受者
     * @return Payload
     */
    public function setAud($aud)
    {
        $this->data['aud'] = $aud;
        return $this;
    }

    /**
     * 获取Aud
     *
     * @return string
     */
    public function getAud()
    {
        return isset($this->data['aud']) ? $this->data['aud'] : null;
    }

    /**
     * 设置jti, web-token提供唯一标识
     *
     * @param string $jti web-token提供唯一标识
     * @return Payload
     */
    public function setJti($jti)
    {
        $this->data['jti'] = $jti;
        return $this;
    }

    /**
     * 获取jti
     *
     * @return string
     */
    public function getJti()
    {
        return isset($this->data['jti']) ? $this->data['jti'] : null;
    }

    /**
     * 设置ext, 扩展数据
     *
     * @param array $ext 扩展数据
     * @return Payload
     */
    public function setExt(array $ext)
    {
        $this->data['ext'] = $ext;
        return $this;
    }

    /**
     * 获取ext
     *
     * @return array
     */
    public function getExt()
    {
        return isset($this->data['ext']) ? $this->data['ext'] : null;
    }

    /**
     * 设置exp, 有效时间
     *
     * @param integer $exp 有效时间
     * @return Payload
     */
    public function setExp($exp)
    {
        $this->exp = $exp;
        return $this;
    }

    /**
     * 获取exp
     *
     * @return integer
     */
    public function getExp()
    {
        return $this->exp;
    }

    /**
     * 设置nbf, 多少秒后生效
     *
     * @param integer $nbf 多少秒后生效
     * @return Payload
     */
    public function setNbf($nbf)
    {
        $this->nbf = $nbf;
        return $this;
    }

    /**
     * 获取nbf
     *
     * @return [type] [description]
     */
    public function getNbf()
    {
        return $this->nbf;
    }

    /**
     * 获取payload数据
     *
     * @return array
     */
    public function getData()
    {
        if (empty($this->data)) {
            throw new JwtException('payload is empty', 4);
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
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * 获取data值
     *
     * @param  string $key  key值
     * @return mixed
     */
    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * isset
     *
     * @param  string $offset  key值
     * @return boolean
     */
    public function offsetExists($offset)
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
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * unset
     *
     * @param  string $offset key值
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}

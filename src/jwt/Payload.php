<?php

namespace mon\auth\jwt;

use ArrayAccess;
use mon\auth\exception\JwtException;

/**
 * JWT payload 数据
 *
 * @author Mon
 * @version v1.0
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
     * @param  string $issuer [description]
     * @return [type]         [description]
     */
    public function setIss(string $issuer)
    {
        $this->data['iss'] = $issuer;
        return $this;
    }

    /**
     * 获取iss
     *
     * @return [type] [description]
     */
    public function getIss()
    {
        return isset($this->data['iss']) ? $this->data['iss'] : null;
    }

    /**
     * 设置sub, 所面向的用户
     *
     * @param  string $issuer [description]
     * @return [type]         [description]
     */
    public function setSub(string $sub)
    {
        $this->data['sub'] = $sub;
        return $this;
    }

    /**
     * 获取sub
     *
     * @return [type] [description]
     */
    public function getSub()
    {
        return isset($this->data['sub']) ? $this->data['sub'] : null;
    }

    /**
     * 设置aud, 接受者
     *
     * @param string $aud [description]
     */
    public function setAud(string $aud)
    {
        $this->data['aud'] = $aud;
        return $this;
    }

    /**
     * 获取Aud
     *
     * @return [type] [description]
     */
    public function getAud()
    {
        return isset($this->data['aud']) ? $this->data['aud'] : null;
    }

    /**
     * 设置jti, web-token提供唯一标识
     *
     * @param string $jti [description]
     */
    public function setJti(string $jti)
    {
        $this->data['jti'] = $jti;
        return $this;
    }

    /**
     * 获取jti
     *
     * @return [type] [description]
     */
    public function getJti()
    {
        return isset($this->data['jti']) ? $this->data['jti'] : null;
    }

    /**
     * 设置ext, 扩展数据
     *
     * @param array $ext [description]
     */
    public function setExt(array $ext)
    {
        $this->data['ext'] = $ext;
        return $this;
    }

    /**
     * 获取ext
     *
     * @return [type] [description]
     */
    public function getExt()
    {
        return isset($this->data['ext']) ? $this->data['ext'] : null;
    }

    /**
     * 设置exp, 有效时间
     *
     * @param int $exp [description]
     */
    public function setExp(int $exp)
    {
        $this->exp = $exp;
        return $this;
    }

    /**
     * 获取exp
     *
     * @return [type] [description]
     */
    public function getExp()
    {
        return $this->exp;
    }

    /**
     * 设置nbf, 多少秒后生效
     *
     * @param int $nbf [description]
     */
    public function setNbf(int $nbf)
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
     * @return [type] [description]
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
     */
    public function __set(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * 获取data值
     *
     * @param  string $key [description]
     * @return [type]      [description]
     */
    public function __get(string $key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * isset
     *
     * @param  [type] $offset [description]
     * @return [type]         [description]
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * get
     *
     * @param  [type] $offset [description]
     * @return [type]         [description]
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * set
     *
     * @param  [type] $offset [description]
     * @param  [type] $value  [description]
     * @return [type]         [description]
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * unset
     *
     * @param  [type] $offset [description]
     * @return [type]         [description]
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}

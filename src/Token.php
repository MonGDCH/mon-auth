<?php
namespace mon\auth;

use mon\env\Config;
use mon\auth\exception\JwtException;

/**
 * 类JWT权限控制
 *
 * @version 1.0.0
 */
class Token
{
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
     * JWT-payload
     *
     * @var array
     */
    protected $payload = [];

    /**
     * 设置iss, 签发者
     *
     * @param  string $issuer [description]
     * @return [type]         [description]
     */
    public function setIss(string $issuer)
    {
        $this->payload['iss'] = $issuer;
        return $this;
    }

    /**
     * 获取iss
     *
     * @return [type] [description]
     */
    public function getIss()
    {
        return $this->payload['iss'] ?? null;
    }

    /**
     * 设置sub, 所面向的用户
     *
     * @param  string $issuer [description]
     * @return [type]         [description]
     */
    public function setSub(string $sub)
    {
        $this->payload['sub'] = $sub;
        return $this;
    }

    /**
     * 获取sub
     *
     * @return [type] [description]
     */
    public function getSub()
    {
        return $this->payload['sub'] ?? null;
    }

    /**
     * 设置jti, web-token提供唯一标识
     *
     * @param string $jti [description]
     */
    public function setJti(string $jti)
    {
        $this->payload['jti'] = $jti;
        return $this;
    }

    /**
     * 获取jti
     *
     * @return [type] [description]
     */
    public function getJti()
    {
        return $this->payload['jti'] ?? null;
    }

    /**
     * 设置ext, 扩展数据
     *
     * @param array $ext [description]
     */
    public function setExt(array $ext)
    {
        $this->payload['ext'] = $ext;
        return $this;
    }

    /**
     * 获取ext
     *
     * @return [type] [description]
     */
    public function getExt()
    {
        return $this->payload['ext'] ?? null;
    }

    /**
     * 设置exp
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
     * 设置nbf
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
     * 清空payload
     *
     * @return [type] [description]
     */
    public function clearPayload()
    {
        $this->payload = [];
        return $this;
    }

    /**
     * 创建签名
     *
     * @param  array       $payload 记录的数据集合
     * @param  string      $key     加密key
     * @param  string      $alg     加密算法
     * @param  int|integer $exp     有效时间，0
     * @return [type]               [description]
     */
    public function create(string $key, string $alg = 'HS256', bool $clear = true)
    {
        if(empty($this->payload)){
            throw new JwtException('payload is empty', 4);
        }
        $header = ['typ' => 'JWT', 'alg' => $alg];
        $payload = $this->payload;
        $payload['iat'] = time();
        // 设置启用时间
        if($this->nbf > 0){
            $payload['nbf'] = $payload['iat'] + $this->nbf;
        }
        // 设置有效时间
        if($this->exp > 0){
            $payload['exp'] = isset($payload['nbf']) ? ($payload['nbf'] + $this->exp) : ($payload['iat'] + $this->exp);
        }

        $info = [];
        $info[] = $this->urlsafeB64Encode(json_encode($header, JSON_UNESCAPED_UNICODE));
        $info[] = $this->urlsafeB64Encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
        
        // 签名
        $data = implode('.', $info);
        $sign = $this->sign($data, $key, $alg);
        $info[] = $this->urlsafeB64Encode($sign);
        
        // 生成jwt
        $jwt = implode('.', $info);
        if($clear){
            $this->clearPayload();
        }
        return $jwt;
    }

    /**
     * 校验JWT
     *
     * @param  string $jwt jwt数据
     * @param  string $key 加密key
     * @param  string $alg 加密算法
     * @return [type]      [description]
     */
    public function check(string $jwt, string $key, string $alg = 'HS256')
    {
        $ticket = explode('.', $jwt);
        if(count($ticket) != 3){
            throw new JwtException('format jwt faild.', 5);
        }
        list($head, $body, $crypt) = $ticket;
        $header = json_decode($this->urlsafeB64Decode($head), true);
        if(!$header){
            throw new JwtException('invalid header encoding', 6);
        }
        $payload = json_decode($this->urlsafeB64Decode($body), true);
        if(!$payload){
            throw new JwtException('invalid payload encoding', 7);
        }
        $sign = json_decode($this->urlsafeB64Decode($crypt), true);
        if(!$sign){
            throw new JwtException('invalid sign encoding', 8);
        }
        if(!isset($this->algs[$header['alg']])){
            throw new JwtException('not found alg', 1);
        }
        if($header['alg'] != $alg){
            throw new JwtException('algorithm not allowed', 9);
        }
    }

    /**
     * 加密签名
     *
     * @param  string $info JSON信息
     * @param  string $key  加密盐
     * @param  string $alg  加密方式
     * @return [type]       [description]
     */
    public function sign(string $info, string $key, string $alg = 'HS256')
    {
        if(!isset($this->algs[$alg])){
            throw new JwtException('not found alg', 1);
        }

        list($type, $algorithm) = $this->algs[$alg];
        switch($type)
        {
            case 'hash_hmac':
                return hash_hmac($algorithm, $info, $key, true);
            case 'openssl':
                $signature = '';
                $success = openssl_sign($info, $signature, $key, $algorithm);
                if(!$success){
                    // 不存在openssl加密扩展
                    throw new JwtException('openssl unable to sign data', 2);
                }
                else{
                    return $signature;
                }
            default:
                throw new JwtException("algorithm type not support", 3);
        }
    }

    /**
     * 验证签名
     *
     * @param  string $info JSON信息
     * @param  string $sign 签名信息
     * @param  string $key  加密盐
     * @param  string $alg  加密方式
     * @return [type]       [description]
     */
    public function verfiy(string $info, string $sign, string $key, string $alg = 'HS256')
    {
        if(!isset($this->algs[$alg])){
            throw new JwtException('not found alg', 1);
        }

        list($type, $algorithm) = $this->algs[$alg];
        switch($function)
        {
            case 'openssl':
                $success = openssl_verify($info, $sign, $key, $algorithm);
                if($success === 1){
                    return true;
                }
                elseif($success === 0){
                    return false;
                }
                throw new JwtException(
                    'openssl error: ' . openssl_error_string(),
                    4
                );
            case 'hash_hmac':
                $hash = hash_hmac($algorithm, $info, $key, true);
                return hash_equals($sign, $hash);
            default:
                throw new JwtException("algorithm type not support", 3);
        }
    }

    /**
     * URL-Base64安全加密
     *
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * URL-Base64安全解密
     *
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if($remainder){
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
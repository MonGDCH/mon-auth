<?php
namespace mon\auth\jwt;

use mon\auth\jwt\Payload;
use mon\auth\exception\JwtException;

/**
 * JWT权限控制
 *
 * @version 1.0.0
 */
class Token
{
    /**
     * 单例实现
     *
     * @var [type]
     */
    protected static $instance;

    /**
     * 支持的加密方式
     *
     * @var [type]
     */
    protected $algs = [
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'HS512' => ['hash_hmac', 'SHA512'],
        'RS256' => ['openssl', 'SHA256'],
        'RS384' => ['openssl', 'SHA384'],
        'RS512' => ['openssl', 'SHA512'],
    ];

    /**
     * 获取单例
     *
     * @return [type] [description]
     */
    public static function instance()
    {
        if(is_null(self::$instance)){
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * 创建签名
     *
     * @param  Payload     $obj     peyload实例
     * @param  string      $key     加密key
     * @param  string      $alg     加密算法
     * @return [type]               [description]
     */
    public function create(Payload $obj, string $key, string $alg = 'HS256')
    {
        $header = ['typ' => 'JWT', 'alg' => $alg];
        $payload = $obj->getData();

        $info = [];
        $info[] = $this->urlsafeB64Encode(json_encode($header, JSON_UNESCAPED_UNICODE));
        $info[] = $this->urlsafeB64Encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
        
        // 签名
        $data = implode('.', $info);
        $sign = $this->sign($data, $key, $alg);
        $info[] = $this->urlsafeB64Encode($sign);
        
        // 生成jwt
        return implode('.', $info);
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
        $sign = $this->urlsafeB64Decode($crypt);
        if(!$sign){
            throw new JwtException('invalid sign encoding', 8);
        }
        // 验证加密方式
        if(!isset($this->algs[$header['alg']])){
            throw new JwtException('not found alg', 1);
        }
        if($header['alg'] != $alg){
            throw new JwtException('algorithm not allowed', 9);
        }
        // 验证签名
        if(!$this->verfiy("{$head}.{$body}", $sign, $key, $alg)){
            throw new  JwtException('check sign faild', 10);
        }
        $now = time();
        // 验证是否在有效期内
        if(isset($payload['nbf']) && $payload['nbf'] > $now){
            throw new JwtException('sign not active', 11);
        }
        if(isset($payload['exp']) && $payload['exp'] < $now){
            throw new JwtException('sign expired', 12);
        }

        return $payload;
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
        switch($type)
        {
            case 'openssl':
                $success = openssl_verify($info, $sign, $key, $algorithm);
                if($success === 1){
                    return true;
                }
                elseif($success === 0){
                    return false;
                }
                throw new JwtException('openssl error: ' . openssl_error_string(), 4);
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

    /**
     * 获取支持的加密方式
     *
     * @return [type] [description]
     */
    public function getAlgs()
    {
        return array_keys($this->algs);
    }
}
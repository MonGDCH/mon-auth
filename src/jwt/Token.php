<?php

declare(strict_types=1);

namespace mon\auth\jwt;

use mon\util\Instance;
use mon\auth\jwt\Payload;
use mon\auth\exception\JwtException;

/**
 * JWT权限控制
 *
 * @author Mon <985558837@qq.com>
 * @version 2.0.0
 */
class Token
{
    use Instance;

    /**
     * 支持的加密方式
     *
     * @var array
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
     * 创建签名
     *
     * @param  Payload $obj  peyload实例
     * @param  string  $key  加密key
     * @param  string  $alg  加密算法
     * @throws JwtException
     * @return string
     */
    public function create(Payload $obj, string $key, string $alg = 'HS256'): string
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
     * @throws JwtException
     * @return array
     */
    public function check(string $jwt, string $key, string $alg = 'HS256'): array
    {
        $ticket = explode('.', $jwt);
        if (count($ticket) != 3) {
            throw new JwtException('格式化jwt数据失败', 5);
        }
        list($head, $body, $crypt) = $ticket;
        $header = json_decode($this->urlsafeB64Decode($head), true);
        if (!$header) {
            throw new JwtException('无效的header编码', 6);
        }
        $payload = json_decode($this->urlsafeB64Decode($body), true);
        if (!$payload) {
            throw new JwtException('无效的payload编码', 7);
        }
        $sign = $this->urlsafeB64Decode($crypt);
        if (!$sign) {
            throw new JwtException('无效的sign无效的', 8);
        }
        // 验证加密方式
        if (!isset($this->algs[$header['alg']])) {
            throw new JwtException('未定义加密方式', 1);
        }
        if ($header['alg'] != $alg) {
            throw new JwtException('加密算法不支持', 9);
        }
        // 验证签名
        if (!$this->verfiy("{$head}.{$body}", $sign, $key, $alg)) {
            throw new JwtException('签名所处', 10);
        }
        $now = time();
        // 验证是否在有效期内
        if (isset($payload['nbf']) && $payload['nbf'] > $now) {
            throw new JwtException('签名已无效', 11);
        }
        if (isset($payload['exp']) && $payload['exp'] < $now) {
            throw new JwtException('签名已过期', 12);
        }

        return $payload;
    }

    /**
     * 加密签名
     *
     * @param  string $info JSON信息
     * @param  string $key  加密盐
     * @param  string $alg  加密方式
     * @throws JwtException
     * @return string
     */
    public function sign(string $info, string $key, string $alg = 'HS256'): string
    {
        if (!isset($this->algs[$alg])) {
            throw new JwtException('未定义加密方式', 1);
        }

        list($type, $algorithm) = $this->algs[$alg];
        switch ($type) {
            case 'hash_hmac':
                return hash_hmac($algorithm, $info, $key, true);
            case 'openssl':
                $signature = '';
                $success = openssl_sign($info, $signature, $key, $algorithm);
                if (!$success) {
                    // 不存在openssl加密扩展
                    throw new JwtException('openssl无法签名数据', 2);
                } else {
                    return $signature;
                }
            default:
                throw new JwtException("加密算法未支持", 3);
        }
    }

    /**
     * 验证签名
     *
     * @param  string $info JSON信息
     * @param  string $sign 签名信息
     * @param  string $key  加密盐
     * @param  string $alg  加密方式
     * @throws JwtException
     * @return boolean
     */
    public function verfiy(string $info, string $sign, string $key, string $alg = 'HS256'): bool
    {
        if (!isset($this->algs[$alg])) {
            throw new JwtException('未定义加密方式', 1);
        }

        list($type, $algorithm) = $this->algs[$alg];
        switch ($type) {
            case 'openssl':
                $success = openssl_verify($info, $sign, $key, $algorithm);
                if ($success === 1) {
                    return true;
                } elseif ($success === 0) {
                    return false;
                }
                throw new JwtException('openssl error: ' . openssl_error_string(), 4);
            case 'hash_hmac':
                $hash = hash_hmac($algorithm, $info, $key, true);
                return hash_equals($sign, $hash);
            default:
                throw new JwtException("加密算法未支持", 3);
        }
    }

    /**
     * URL-Base64安全加密
     *
     * @param  string $input 加密字符串
     * @return string
     */
    public function urlsafeB64Encode(string $input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * URL-Base64安全解密
     *
     * @param  string $input 解密字符串
     * @return string
     */
    public function urlsafeB64Decode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * 获取支持的加密方式
     *
     * @return array
     */
    public function getAlgs(): array
    {
        return array_keys((array) $this->algs);
    }
}

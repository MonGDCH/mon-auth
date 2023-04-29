<?php

declare(strict_types=1);

namespace mon\auth\jwt\driver;

use mon\util\Event;
use mon\util\Instance;
use mon\auth\jwt\driver\Payload;
use mon\auth\exception\JwtException;
use mon\auth\exception\AuthException;

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
     * 解析获取jwt数据
     *
     * @param  string $jwt jwt数据
     * @param  string $key 加密key
     * @param  string $alg 加密算法
     * @throws JwtException
     * @return array
     */
    public function parse(string $jwt, string $key, string $alg = 'HS256'): array
    {
        $ticket = explode('.', $jwt);
        if (count($ticket) != 3) {
            throw new JwtException('无效的格式', JwtException::JWT_TOKEN_FORMAT_ERROR);
        }
        list($head, $body, $crypt) = $ticket;
        $header = json_decode($this->urlsafeB64Decode($head), true);
        if (!$header) {
            throw new JwtException('无效的header编码', JwtException::JWT_TOKEN_HEADER_ERROR);
        }
        $payload = json_decode($this->urlsafeB64Decode($body), true);
        if (!$payload) {
            throw new JwtException('无效的payload编码', JwtException::JWT_TOKEN_PAYLOAD_ERROR);
        }
        $sign = $this->urlsafeB64Decode($crypt);
        if (!$sign) {
            throw new JwtException('无效的sign签名编码', JwtException::JWT_TOKEN_SIGN_ERROR);
        }
        // 验证加密方式
        if (!isset($this->algs[$header['alg']])) {
            throw new JwtException('未定义加密方式', JwtException::JWT_ALG_NOT_FOUND);
        }
        if ($header['alg'] != $alg) {
            throw new JwtException('加密算法不支持', JwtException::JWT_ALG_NOT_SUPPORT);
        }
        // 验证签名有效性
        if (!$this->examine("{$head}.{$body}", $sign, $key, $alg)) {
            throw new JwtException('Token签名错误', JwtException::JWT_TOKEN_VERIFY_ERROR);
        }

        return $payload;
    }

    /**
     * 验证jwt-payload
     *
     * @param string $payload   jwt的payload内容
     * @throws JwtException
     * @return boolean
     */
    public function verify(array $payload): bool
    {
        $now = time();
        // 验证是否在有效期内
        if (isset($payload['nbf']) && $payload['nbf'] > $now) {
            throw new JwtException('Token签名未生效', JwtException::JWT_TOKEN_NBF_ERROR);
        }
        if (isset($payload['exp']) && $payload['exp'] < $now) {
            throw new JwtException('Token签名已过期', JwtException::JWT_TOKEN_EXP_ERROR);
        }

        // 触发jwt验证事件，回调方法可通过 throw JwtException 增加自定义的验证方式
        Event::instance()->trigger('jwt_check', $payload);

        return true;
    }

    /**
     * 验证jwt有效性,整合parse、verify方法
     *
     * @param  string $jwt jwt数据
     * @param  string $key 加密key
     * @param  string $alg 加密算法
     * @throws JwtException
     * @return boolean
     */
    public function check(string $jwt, string $key, string $alg = 'HS256'): bool
    {
        // 获取jwt数据
        $payload = $this->parse($jwt, $key, $alg);
        // 校验
        return $this->verify($payload);
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
            throw new JwtException('未定义加密方式', JwtException::JWT_ALG_NOT_FOUND);
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
                    throw new JwtException('openssl无法签名数据', AuthException::OPENSSL_ERROR);
                } else {
                    return $signature;
                }
            default:
                throw new JwtException("加密算法未支持", JwtException::JWT_ALG_NOT_SUPPORT);
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

    /**
     * 验证签名有效性
     *
     * @param  string $info JSON信息
     * @param  string $sign 签名信息
     * @param  string $key  加密盐
     * @param  string $alg  加密方式
     * @throws JwtException
     * @return boolean
     */
    protected function examine(string $info, string $sign, string $key, string $alg = 'HS256'): bool
    {
        if (!isset($this->algs[$alg])) {
            throw new JwtException('未定义加密方式', JwtException::JWT_ALG_NOT_FOUND);
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
                throw new JwtException('openssl error: ' . openssl_error_string(), AuthException::OPENSSL_ERROR);
            case 'hash_hmac':
                $hash = hash_hmac($algorithm, $info, $key, true);
                return hash_equals($sign, $hash);
            default:
                throw new JwtException("加密算法未支持", JwtException::JWT_ALG_NOT_SUPPORT);
        }
    }
}

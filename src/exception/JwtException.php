<?php

namespace mon\auth\exception;

/**
 * JWT异常
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class JwtException extends AuthException
{
    /**
     * openssl错误
     */
    const OPENSSL_ERROR = 10000;

    /**
     * 未定义加密方式
     */
    const ALG_NOT_FOUND = 10001;

    /**
     * 加密方式不支持
     */
    const ALG_NOT_SUPPORT = 10002;

    /**
     * JWT格式错误
     */
    const JWT_FORMAT_ERROR = 10003;

    /**
     * header编码错误
     */
    const JWT_HEADER_ERROR = 10004;

    /**
     * payload编码错误
     */
    const JWT_PAYLOAD_ERROR = 10005;

    /**
     * sign签名编码错误
     */
    const JWT_SIGN_ERROR = 10006;

    /**
     * token验证不通过
     */
    const TOKEN_VERIFY_ERROR = 10007;

    /**
     * token未生效
     */
    const TOKEN_NBF_ERROR = 10008;

    /**
     * token已过期
     */
    const TOKEN_EXP_ERROR = 10009;

    /**
     * token内容不能为空
     */
    const PAYLOAD_NOT_EMPTY = 10010;

    /**
     * payload的iss错误
     */
    const PAYLOAD_ISS_ERROR = 10010;

    /**
     * payload的sub错误
     */
    const PAYLOAD_SUB_ERROR = 10011;

    /**
     * 未知异常
     */
    const JWT_CHECK_EXCEPTION = 20000;
}

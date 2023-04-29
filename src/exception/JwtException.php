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
     * jwt未知异常
     */
    const JWT_EXCEPTION = 30000;

    /**
     * 未定义加密方式
     */
    const JWT_ALG_NOT_FOUND = 30100;

    /**
     * 加密方式不支持
     */
    const JWT_ALG_NOT_SUPPORT = 30110;

    /**
     * jwt-Token格式错误
     */
    const JWT_TOKEN_FORMAT_ERROR = 30200;

    /**
     * jwt-header编码错误
     */
    const JWT_TOKEN_HEADER_ERROR = 30210;

    /**
     * jwt-payload编码错误
     */
    const JWT_TOKEN_PAYLOAD_ERROR = 30220;

    /**
     * jwt-sign签名编码错误
     */
    const JWT_TOKEN_SIGN_ERROR = 30230;

    /**
     * jwt-token验证不通过
     */
    const JWT_TOKEN_VERIFY_ERROR = 30300;

    /**
     * jwt-token未生效
     */
    const JWT_TOKEN_NBF_ERROR = 30310;

    /**
     * jwt-token已过期
     */
    const JWT_TOKEN_EXP_ERROR = 30320;

    /**
     * jwt-token-payload不能为空
     */
    const JWT_PAYLOAD_NOT_EMPTY = 30400;

    /**
     * jwt-token-payload的iss错误
     */
    const JWT_PAYLOAD_ISS_ERROR = 30410;

    /**
     * jwt-token-payload的sub错误
     */
    const JWT_PAYLOAD_SUB_ERROR = 30420;
}

<?php

declare(strict_types=1);

namespace mon\auth\exception;

/**
 * API相关异常
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class APIException extends AuthException
{
    /**
     * 无效AccessToken失败
     */
    const ACCESS_TOKEN_ERROR = 10001;

    /**
     * AccessToken已过期
     */
    const ACCESS_TOKEN_INVALID = 10002;

    /**
     * AccessToken App_id错误
     */
    const ACCESS_TOKEN_FAILD = 10003;

    /**
     * 签名字段不存在
     */
    const SIGN_NOT_FOUND = 20001;

    /**
     * 签名验证错误
     */
    const SIGN_VERIFY_FAIL = 20002;

    /**
     * 签名已过期
     */
    const SIGN_TIME_INVALID = 20003;
}

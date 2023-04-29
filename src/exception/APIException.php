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
    const ACCESS_TOKEN_ERROR = 40100;

    /**
     * AccessToken已过期
     */
    const ACCESS_TOKEN_INVALID = 40110;

    /**
     * AccessToken App_id错误
     */
    const ACCESS_TOKEN_FAILD = 40120;

    /**
     * 签名字段不存在
     */
    const SIGN_NOT_FOUND = 40200;

    /**
     * 签名验证错误
     */
    const SIGN_VERIFY_FAIL = 40210;

    /**
     * 签名已过期
     */
    const SIGN_TIME_INVALID = 40220;

    /**
     * APPID不存在
     */
    const APPID_NOT_FOUND = 40300;

    /**
     * APPID无效
     */
    const APPID_STATUS_ERROR = 40310;

    /**
     * APPID已过期
     */
    const APPID_TIME_INVALID = 40320;

    /**
     * 签名APPID参数错误
     */
    const APPID_PARAMS_FAILD = 40330;
}

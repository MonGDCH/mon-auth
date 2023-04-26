<?php

namespace mon\auth\exception;

/**
 * RBAC异常
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class RbacException extends AuthException
{
    /**
     * 权限模块未初始化
     */
    const AUTH_INIT_ERROR = 10000;

    /**
     * 权限模型不存在
     */
    const AUTH_MODEL_NOT_FOUND = 10001;

    /**
     * 权限规则不支持
     */
    const AUTH_RULE_NOT_SUPPORT = 10002;
}

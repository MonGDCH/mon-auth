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
     * 权限模型不存在
     */
    const RBAC_MODEL_NOT_FOUND = 20100;

    /**
     * 权限规则不支持
     */
    const RBAC_RULE_NOT_SUPPORT = 20200;
}

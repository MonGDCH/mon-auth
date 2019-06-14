<?php
namespace mon\auth\rbac;

use mon\util\Instance;

/**
 * RBAC权限使用
 */
class Rbac
{
    use Instance;

    /**
     * 验证权限
     *
     * @param integer $uid
     * @param string $mark
     * @param boolean $relation
     * @return void
     */
    public function check(int $uid, string $mark, bool $relation = false)
    {

    }

    /**
     * 获取用户所在组别
     *
     * @param integer $uid 用户ID
     * @return void
     */
    public function getUserGroup(int $uid)
    {

    }

    /**
     * 获取用户权限列表
     *
     * @param integer $uid 用户ID
     * @return void
     */
    public function getUserRule(int $uid)
    {

    }
}
<?php

namespace mon\auth\rbac;

use mon\util\Instance;
use mon\auth\rbac\model\Access;
use mon\auth\rbac\model\Rule;

/**
 * RBAC权限使用
 */
class Rbac
{
    use Instance;

    /**
     * 权限规则
     *
     * @var array
     */
    protected $rules = [];

    /**
     * 验证权限
     *
     * @param integer $uid      用户ID
     * @param string  $mark     规则标志位
     * @param boolean $relation 如果为 true 表示满足任一条规则即通过验证;如果为 false 则表示需满足所有规则才能通过验证
     * @return void
     */
    public function check($uid, $mark, $relation = false)
    {
        // 获取用户需要验证的所有有效规则列表
        $authList = $this->getUserAuth($uid);
        if (in_array("*", (array) $authList)) {
            // 具备所有权限
            return true;
        }

        if (is_string($mark)) {
            $mark = strtolower($mark);
            if (strpos($mark, ',') !== false) {
                $mark = explode(',', $mark);
            } else {
                $mark = str_replace('.', '/', $mark);
                $mark = array($mark);
            }
        }
        // 保存验证通过的规则名
        $list = array();


        foreach ($authList as $auth) {
            if (in_array($auth, $mark)) {
                $list[] = $auth;
            }
        }

        if (true == $relation && !empty($list)) {
            return true;
        }
        $diff = array_diff($mark, $list);
        if (false == $relation && empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * 获取用户权限列表
     *
     * @param integer $uid
     * @return void
     */
    public function getUserAuth($uid)
    {
        static $_auths = array();
        if (isset($_auths[$uid])) {
            return $_auths[$uid];
        }
        // 判断登录验证
        // if ($this->config['auth_type'] == 2 && Session::has('_rule_list_' . $uid)) {
        //     return Session::get('_rule_list_' . $uid);
        // }

        // 获取规则节点
        $ids = $this->getUserRule($uid);
        if (empty($ids)) {
            $_auths[$uid] = array();
            return array();
        }

        // 构造查询条件
        $map['status'] = 1;
        if (!in_array('*', (array) $ids)) {
            $map['id'] = ['in', $ids];
        }
        $this->rules = Rule::instance()->where($map)->field('id, pid, mark, name, description')->select();

        //循环规则，判断结果。
        $authList = array();
        // 判断是否具备所有权限
        if (in_array('*', (array) $ids)) {
            $authList[] = "*";
        }
        foreach ($this->rules as $rule) {
            $authList[$rule['id']] = strtolower($rule['name']);
        }
        $_auths[$uid] = $authList;
        //登录验证则需要保存规则列表
        // if (2 == $this->config['auth_type']) {
        //     //规则列表结果保存到session
        //     Session::set('_rule_list_' . $uid, $authList);
        // }
        return array_unique($authList);
    }

    /**
     * 获取用户权限列表
     *
     * @param integer $uid 用户ID
     * @return void
     */
    public function getUserRule(int $uid)
    {
        $groups = Access::instance()->getUserGroup($uid);
        $ids = [];
        foreach ($groups as $v) {
            $ids = array_merge($ids, explode(',', trim($v['rules'], ',')));
        }
        $ids = array_unique($ids);
        return $ids;
    }
}

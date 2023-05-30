<?php

namespace mon\auth\rbac\model;

use mon\util\Tree;
use mon\util\Instance;
use mon\auth\rbac\Auth;
use mon\orm\exception\DbException;

/**
 * 角色组模型
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.1   优化代码
 */
class Group extends Base
{
    use Instance;

    /**
     * 表名
     *
     * @var string
     */
    protected $table;

    /**
     * 构造方法
     *
     * @param Auth $auth Auth实例
     */
    public function __construct(Auth $auth)
    {
        parent::__construct($auth);
        $this->table = $this->auth->getConfig('auth_group');
    }

    /**
     * 获取角色组信息
     *
     * @param array $where where条件
     * @param array $field 查询字段
     * @return array|false
     */
    public function getInfo(array $where, array $field = ['*'])
    {
        $info = $this->where($where)->field($field)->find();
        if (!$info) {
            $this->error = '角色组不存在';
            return false;
        }

        return $info;
    }

    /**
     * 获取组别信息
     *
     * @param array $option 分页参数 
     * @param array $where  查询条件
     * @return array
     */
    public function getList(array $option, array $where = []): array
    {
        $page = isset($option['page']) ? intval($option['page']) : 1;
        $limit = isset($option['limit']) ? intval($option['limit']) : 10;

        $list = $this->page($page, $limit)->where($where)->select();
        $count = $this->where($where)->count('id');

        return [
            'list' => $list,
            'count' => $count
        ];
    }

    /**
     * 创建角色组
     *
     * @param array $option 组别参数
     * @param array $ext    扩展写入字段
     * @return integer|false
     */
    public function add(array $option, array $ext = [])
    {
        $check = $this->validate()->scope('group_add')->data($option)->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }
        // 去除重复的规则
        $rules = array_unique($option['rules']);
        sort($rules);
        // 判断验证组别权限
        if (!$this->diffRuleForPid($option['pid'], $rules)) {
            return false;
        }
        // 记录组别信息
        $info = array_merge($ext, [
            'title' => $option['title'],
            'pid'   => $option['pid'],
            'rules' => implode(',', $rules),
        ]);
        $group_id = $this->save($info, null, true);
        if (!$group_id) {
            $this->error = '创建权限组失败';
            return false;
        }

        return $group_id;
    }

    /**
     * 修改角色组信息
     *
     * @param array $option 组别参数
     * @param array $ext    扩展写入字段
     * @return boolean
     */
    public function modify(array $option, array $ext = []): bool
    {
        $check = $this->validate()->scope('group_modify')->data($option)->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }
        // 获取数据
        $info = $this->getInfo(['id' => $option['idx']]);
        if (!$info) {
            return false;
        }
        $modifyRule = false;
        $status = $option['status'];
        $idx = $option['idx'];
        $pid = $option['pid'];
        $rules = array_unique($option['rules']);
        sort($rules);

        // 判断是否修改了规则或者修改了父级
        if ($this->modifyRule($info['rules'], $rules) || $pid != $info['pid']) {
            // 修改了权限，判断是否与上级权限存在冲突
            if (!$this->diffRuleForPid($pid, $rules)) {
                // 存在越级权限
                return false;
            }
            // 标志修改了规则
            $modifyRule = true;
        }

        // 更新数据
        $this->startTrans();
        try {
            // 获取所有组别信息
            $groups = $this->select();
            // 判断是否修改规则，修改了规则，更新移除后代多余的规则
            if ($modifyRule) {
                // 比对每一个后代，有规则冲突则更新
                $childrens = Tree::instance()->data($groups)->getChildren($idx);
                foreach ($childrens as $child) {
                    // 比对子级与父级的权限
                    if (!empty($this->diffRule($rules, $child['rules']))) {
                        $newChildRule = $this->intersectRule($rules, $child['rules']);
                        $saveChildRule = $this->save(['rules' => implode(',', $newChildRule)], ['id' => $child['id']]);
                        if (!$saveChildRule) {
                            $this->rollback();
                            $this->error = '更新后代角色组权限规则失败';
                            return false;
                        }
                    }
                }
            }

            // 判断是否修改了状态
            if ($info['status'] != $status) {
                // 修改为有效
                if ($status == $this->auth->getConfig('effective_status')) {
                    // 有效则判断当前节点所有祖先节点是否都为有效状态。
                    $parents = Tree::instance()->data($groups)->getParents($idx);
                    foreach ($parents as $parent) {
                        if ($parent['status'] == $this->auth->getConfig('invalid_status')) {
                            $this->rollback();
                            $this->error = '操作失败(祖先节点存在无效节点)';
                            return false;
                        }
                    }

                    // 更新角色组信息
                    $modifyInfo = array_merge($ext, [
                        'title'     => $option['title'],
                        'pid'       => $pid,
                        'rules'     => $rules,
                        'status'    => $status
                    ]);
                    $save = $this->save($modifyInfo, ['id' => $idx]);
                    if (!$save) {
                        $this->rollback();
                        $this->error = '修改角色组信息失败';
                        return false;
                    }
                } else if ($status == $this->auth->getConfig('invalid_status')) {
                    // 修改为无效状态
                    $modifyInfo = array_merge($ext, [
                        'title'     => $option['title'],
                        'pid'       => $pid,
                        'rules'     => implode(',', $rules),
                        'status'    => $status
                    ]);
                    $save = $this->save($modifyInfo, ['id' => $idx]);
                    if (!$save) {
                        $this->rollback();
                        $this->error = '修改当前角色组信息失败';
                        return false;
                    }

                    // 无效，同步将所有后代节点下线
                    $childrens = Tree::instance()->data($groups)->getChildrenIds($idx);
                    // 下线后代
                    if ($childrens) {
                        $offline = $this->whereIn('id', $childrens)->update(['status' => $option['status'], 'update_time' => time()]);
                        if (!$offline) {
                            $this->rollback();
                            $this->error = '修改后代权限规则失败';
                            return false;
                        }
                    }
                } else {
                    // 未知状态
                    $this->rollback();
                    $this->error = '未知状态';
                    return false;
                }
            } else {
                // 未修改状态，直接更新
                $modifyInfo = array_merge($ext, [
                    'title'     => $option['title'],
                    'pid'       => $pid,
                    'rules'     => implode(',', $rules),
                    'status'    => $status
                ]);
                $save = $this->save($modifyInfo, ['id' => $idx]);
                if (!$save) {
                    $this->rollback();
                    $this->error = '修改角色组信息失败';
                    return false;
                }
            }

            $this->commit();
            return true;
        } catch (DbException $e) {
            // 回滚事务
            $this->rollback();
            $this->error = '修改角色组信息异常, ' . $e->getMessage();
            return false;
        }
    }

    /**
     * 校验子级权限是否越权父级
     *
     * @param integer|string  $pid  父级组别ID
     * @param array $rules  子级权限或要验证的权限列表
     * @return boolean
     */
    protected function diffRuleForPid($pid, array $rules): bool
    {
        // 存在父级组别，子级组别权限规则必须包含在父级权限规则中
        if ($pid > 0) {
            $parentInfo = $this->getInfo(['id' => $pid]);
            if (!$parentInfo) {
                $this->error = '父级权限组别不存在';
                return false;
            }
            // 比对规则
            $flag = $this->diffRule($parentInfo['rules'], $rules);
            if (!empty($flag)) {
                $this->error = '子级存在越级权限[' . implode(',', $flag) . ']';
                return false;
            }
        }

        return true;
    }

    /**
     * 比对规则，校验是否为子级关系，获取越级的权限
     *
     * @param array|string $baseRule   被比较的规则数组
     * @param array|string $checkRule  比较的规则数组
     * @return array    越级的规则
     */
    protected function diffRule($baseRule, $checkRule): array
    {
        if (is_string($baseRule)) {
            $baseRule = explode(',', $baseRule);
            $baseRule = array_unique($baseRule);
            sort($baseRule);
        }
        if (is_string($checkRule)) {
            $checkRule = explode(',', $checkRule);
            $checkRule = array_unique($checkRule);
            sort($checkRule);
        }

        // 判断父级是否存在超级管理员权限标志位，不存在则判断子级是否存在越级的权限
        if (!in_array($this->auth->getConfig('admin_mark'), $baseRule)) {
            // 比对数组
            return array_diff($checkRule, $baseRule);
        }

        return [];
    }

    /**
     * 比对规则，校验获取子级规则（交集）
     *
     * @param array|string $baseRule    被比较的规则数组
     * @param array|string $check       比较的规则数组
     * @return array 子级规则数组
     */
    protected function intersectRule($baseRule, $checkRule): array
    {
        if (is_string($baseRule)) {
            $baseRule = explode(',', $baseRule);
            $baseRule = array_unique($baseRule);
            sort($baseRule);
        }
        if (is_string($checkRule)) {
            $checkRule = explode(',', $checkRule);
            $checkRule = array_unique($checkRule);
            sort($checkRule);
        }

        // 判断父级是否存在超级管理员权限标志位，管理员则返回所有子级权限
        if (!in_array($this->auth->getConfig('admin_mark'), $baseRule)) {
            // 获取交集
            $rules = array_intersect($baseRule, $checkRule);
            sort($rules);
            return $rules;
        }

        return $checkRule;
    }

    /**
     * 判断是否修改了规则
     *
     * @param string|array $baseRule  被比较的规则
     * @param string|array $newRule   新的规则
     * @return boolean
     */
    protected function modifyRule($baseRule, $newRule): bool
    {
        // 整理数据成字符串
        if (is_array($baseRule)) {
            $baseRule = array_unique((array) $baseRule);
            sort($baseRule);
            $baseRule = implode(',', $baseRule);
        }
        if (is_array($newRule)) {
            $newRule = array_unique((array) $newRule);
            sort($newRule);
            $newRule = implode(',', $newRule);
        }

        return $baseRule != $newRule;
    }
}

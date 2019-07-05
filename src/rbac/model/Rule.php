<?php

namespace mon\auth\rbac\model;

use mon\util\Instance;
use mon\auth\rbac\model\Comm;
use mon\util\Tree;

/**
 * 权限规则表
 */
class Rule extends Comm
{
    use Instance;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'mon_auth_rule';

    /**
     * 获取规则信息
     *
     * @param integer $gid
     * @return void
     */
    public function getInfo(int $rule_id)
    {
        return $this->where('id', $rule_id)->get();
    }

    /**
     * 新增规则
     *
     * @param arrry $option
     * @return void 新增规则ID
     */
    public function add(arrry $option)
    {
        $check = $this->validate->scope('rule_add')->data($option)->check();
        if ($check !== true) {
            $this->error = $check;
            return false;
        }

        $rule_id = $this->save([
            'pid'           => $option['pid'],
            'mark'          => $option['mark'],
            'name'          => $option['name'],
            'description'   => $option['description'] ?? '',
        ], null, true);
        if (!$rule_id) {
            $this->error = '新增规则失败';
            return false;
        }

        return $rule_id;
    }

    /**
     * 修改规则
     *
     * @param array $option
     * @return void
     */
    public function modify(array $option)
    {
        $check = $this->validate->scope('rule_modify')->data($option)->check();
        if ($check !== true) {
            $this->error = $check;
            return false;
        }

        $idx = $option['idx'];
        $status = $option['status'];
        $baseInfo = $this->getInfo($idx);
        if ($baseInfo->isEmpty()) {
            $this->error = '规则节点不存在';
            return false;
        }

        if ($baseInfo->status != $status) {
            // 修改了状态
            $rules = $this->select();
            if ($status == '1') {
                // 有效则判断当前节点所有祖先节点是否都为有效状态。
                $parents = Tree::instance()->data($rules)->getParents($idx);
                foreach ($parents as $v) {
                    if ($v['status'] == 2) {
                        $this->error = '操作失败(祖先节点存在无效节点)';
                        return false;
                    }
                }

                // 更新
                $save = $this->save([
                    'pid'           => $option['pid'],
                    'mark'          => $option['mark'],
                    'name'          => $option['name'],
                    'description'   => $option['description'] ?? '',
                    'status'        => $option['status'],
                ], ['id' => $idx]);
                if (!$save) {
                    $this->error = '更新规则失败';
                    return false;
                }

                return true;
            } else if ($status == '0') {
                // 无效，同步将所有后代节点下线
                $childrens = Tree::instance()->data($rules)->getChildrenIds($idx);

                // 更新
                $this->startTrans();
                try {
                    // 更新规则
                    $save = $this->save([
                        'pid'           => $option['pid'],
                        'mark'          => $option['mark'],
                        'name'          => $option['name'],
                        'description'   => $option['description'] ?? '',
                        'status'        => $option['status'],
                    ], ['id' => $idx]);
                    if (!$save) {
                        $this->rollback();
                        $this->error = '更新失败';
                        return false;
                    }


                    // 下线后代
                    $offline = $this->whereIn('id', $childrens)->update(['status' => $option['status'], 'update_time' => $_SERVER['REQUEST_TIME']]);
                    if (!$offline) {
                        $this->rollback();
                        $this->error = '修改后代权限规则失败';
                        return false;
                    }

                    // 提交事务
                    $this->commit();
                    return true;
                } catch (Exception $e) {
                    // 回滚事务
                    $this->rollback();
                    $this->error = '修改规则异常, ' . $e->getMessage();
                    return false;
                }
            }
        } else {
            // 未修改状态，直接更新
            $save = $this->save([
                'pid'           => $option['pid'],
                'mark'          => $option['mark'],
                'name'          => $option['name'],
                'description'   => $option['description'] ?? '',
                'status'        => $option['status'],
            ], ['id' => $idx]);
            if (!$save) {
                $this->error = '更新失败';
                return false;
            }

            return true;
        }
    }
}

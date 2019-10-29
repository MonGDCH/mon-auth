<?php

namespace mon\auth\rbac\model;

use mon\auth\rbac\Auth;
use mon\util\Instance;
use mon\util\Tree;

/**
 * 权限规则表
 */
class Rule extends Base
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
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = Auth::instance()->getConfig('auth_rule');
    }

    /**
     * 获取规则信息
     *
     * @param array $where
     * @return void
     */
    public function getInfo(array $where)
    {
        $info = $this->where($where)->find();
        if (!$info) {
            $this->error = '规则信息不存在';
            return false;
        }

        return $info;
    }

    /**
     * 获取所有组别信息
     *
     * @return void
     */
    public function getList(array $option)
    {
        $offset = isset($option['offset']) ? intval($option['offset']) : 0;
        $limit = isset($option['limit']) ? intval($option['limit']) : 10;

        $list = $this->limit($offset * $limit, $limit)->select();
        $count = $this->count('id');

        return [
            'list' => $list,
            'count' => $count
        ];
    }


    /**
     * 新增规则
     *
     * @param array $option 规则参数
     * @param array $ext    扩展写入字段
     * @return void
     */
    public function add(array $option, array $ext = [])
    {
        $check = $this->validate->scope('rule_add')->data($option)->check();
        if ($check !== true) {
            $this->error = $check;
            return false;
        }

        $info = array_merge($ext, [
            'pid'           => $option['pid'],
            'title'         => $option['title'],
            'name'          => $option['name'],
            'remark'        => isset($option['remark']) ? $option['remark'] : '',
        ]);
        $rule_id = $this->save($info, null, true);
        if (!$rule_id) {
            $this->error = '新增规则失败';
            return false;
        }

        return $rule_id;
    }

    /**
     * 修改规则
     *
     * @param array $option 规则参数
     * @param array $ext    扩展写入字段
     * @return void
     */
    public function modify(array $option, array $ext = [])
    {
        $check = $this->validate->scope('rule_modify')->data($option)->check();
        if ($check !== true) {
            $this->error = $check;
            return false;
        }

        $idx = $option['idx'];
        $status = $option['status'];
        $baseInfo = $this->getInfo(['id' => $idx]);
        if (!$baseInfo) {
            return false;
        }

        if ($baseInfo['status'] != $status) {
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
                $info = array_merge($ext, [
                    'pid'           => $option['pid'],
                    'title'         => $option['title'],
                    'name'          => $option['name'],
                    'remark'        => isset($option['remark']) ? $option['remark'] : '',
                    'status'        => $option['status'],
                ]);
                $save = $this->save($info, ['id' => $idx]);
                if (!$save) {
                    $this->error = '更新规则失败';
                    return false;
                }

                return true;
            } else if ($status == '2') {
                // 无效，同步将所有后代节点下线
                $childrens = Tree::instance()->data($rules)->getChildrenIds($idx);

                // 更新
                $this->startTrans();
                try {
                    // 更新规则
                    $info = array_merge($ext, [
                        'pid'           => $option['pid'],
                        'title'         => $option['title'],
                        'name'          => $option['name'],
                        'remark'        => isset($option['remark']) ? $option['remark'] : '',
                        'status'        => $option['status'],
                    ]);
                    $save = $this->save($info, ['id' => $idx]);
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
            $info = array_merge($ext, [
                'pid'           => $option['pid'],
                'title'         => $option['title'],
                'name'          => $option['name'],
                'remark'        => isset($option['remark']) ? $option['remark'] : '',
            ]);
            $save = $this->save($info, ['id' => $idx]);
            if (!$save) {
                $this->error = '更新失败';
                return false;
            }

            return true;
        }
    }
}

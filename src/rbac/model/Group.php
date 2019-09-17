<?php

namespace mon\auth\rbac\model;

use mon\auth\rbac\Auth;
use mon\util\Instance;

/**
 * 角色组模型
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
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = Auth::instance()->getConfig('auth_group');
    }

    /**
     * 获取角色组信息
     *
     * @param array $where
     * @return void
     */
    public function getInfo(array $where)
    {
        $info = $this->where($where)->find();
        if (!$info) {
            $this->error = '角色组不存在';
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
     * 创建角色组
     *
     * @param array $option
     * @return void
     */
    public function add(array $option)
    {
        $check = $this->validate->scope('group_add')->data($option)->check();
        if ($check !== true) {
            $this->error = $check;
            return false;
        }

        $save = $this->save([
            'name'  => $option['name'],
            'pid'   => $option['pid'],
            'rules' => $option['rules'],
        ]);
        if (!$save) {
            $this->error = '创建权限组失败';
            return false;
        }

        return true;
    }

    /**
     * 修改角色组信息
     *
     * @param array $option
     * @return void
     */
    public function modify(array $option)
    {
        $check = $this->validate->scope('group_modify')->data($option)->check();
        if ($check !== true) {
            $this->error = $check;
            return false;
        }
        $info = $this->getInfo(['id' => $option['idx']]);
        if (!$info) {
            return false;
        }

        $status = $option['status'];
        $idx = $option['idx'];
        if ($info['status'] != $status) {
            // 修改了状态
            $groups = $this->select();
            if ($status == '1') {
                // 有效则判断当前节点所有祖先节点是否都为有效状态。
                $parents = Tree::instance()->data($groups)->getParents($idx);
                foreach ($parents as $v) {
                    if ($v['status'] == 2) {
                        $this->error = '操作失败(祖先节点存在无效节点)';
                        return false;
                    }
                }

                // 更新
                $save = $this->save([
                    'name'      => $option['name'],
                    'pid'       => $option['pid'],
                    'rules'     => $option['rules'],
                    'status'    => $option['status']
                ], ['id' => $idx]);
                if (!$save) {
                    $this->error = '修改角色组信息失败';
                    return false;
                }

                return true;
            } else if ($status == '2') {
                // 无效，同步将所有后代节点下线
                $childrens = Tree::instance()->data($groups)->getChildrenIds($idx);

                // 更新
                $this->startTrans();
                try {
                    // 更新规则
                    $save = $this->save([
                        'name'      => $option['name'],
                        'pid'       => $option['pid'],
                        'rules'     => $option['rules'],
                        'status'    => $option['status']
                    ], ['id' => $idx]);
                    if (!$save) {
                        $this->rollback();
                        $this->error = '修改当前角色组信息失败';
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
                    $this->error = '修改角色组信息异常, ' . $e->getMessage();
                    return false;
                }
            }
        } else {
            // 未修改状态，直接更新
            $save = $this->save([
                'name'      => $option['name'],
                'pid'       => $option['pid'],
                'rules'     => $option['rules'],
                'status'    => $option['status']
            ], ['id' => $option['idx']]);
            if (!$save) {
                $this->error = '修改角色组信息失败';
                return false;
            }

            return true;
        }
    }
}

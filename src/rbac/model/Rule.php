<?php

namespace mon\auth\rbac\model;

use mon\auth\rbac\Auth;
use mon\util\Instance;
use mon\orm\Model;
use mon\util\Tree;
use mon\auth\rbac\Validate;

/**
 * 权限规则表
 */
class Rule extends Model
{
    use Instance;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'auth_rule';

    /**
     * 新增自动写入字段
     *
     * @var array
     */
    protected $insert = ['create_time', 'update_time'];

    /**
     * 更新自动写入字段
     *
     * @var array
     */
    protected $update = ['update_time'];

    /**
     * 验证器
     *
     * @var [type]
     */
    protected $validate;

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->table = Auth::instance()->getConfig('auth_rule');
        $this->validate = new Validate;
    }

    /**
     * 自动完成update_time字段
     * 
     * @param [type] $val 默认值
     * @param array  $row 列值
     */
    protected function setUpdateTimeAttr($val)
    {
        return $_SERVER['REQUEST_TIME'];
    }

    /**
     * 自动完成create_time字段
     * 
     * @param [type] $val 默认值
     * @param array  $row 列值
     */
    protected function setCreateTimeAttr($val)
    {
        return $_SERVER['REQUEST_TIME'];
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
            'title'         => $option['title'],
            'name'          => $option['name'],
            'remark'        => isset($option['remark']) ? $option['remark'] : '',
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
                $save = $this->save([
                    'pid'           => $option['pid'],
                    'mark'          => $option['mark'],
                    'name'          => $option['name'],
                    'remark'        => isset($option['remark']) ? $option['remark'] : '',
                    'status'        => $option['status'],
                ], ['id' => $idx]);
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
                    $save = $this->save([
                        'pid'           => $option['pid'],
                        'mark'          => $option['mark'],
                        'name'          => $option['name'],
                        'remark'        => isset($option['remark']) ? $option['remark'] : '',
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
                'remark'        => isset($option['remark']) ? $option['remark'] : '',
            ], ['id' => $idx]);
            if (!$save) {
                $this->error = '更新失败';
                return false;
            }

            return true;
        }
    }
}

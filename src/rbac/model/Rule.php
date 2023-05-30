<?php

namespace mon\auth\rbac\model;

use mon\util\Tree;
use mon\util\Instance;
use mon\auth\rbac\Auth;
use mon\orm\exception\DbException;

/**
 * 权限规则表
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.1   优化代码
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
     *
     * @param Auth $auth Auth实例
     */
    public function __construct(Auth $auth)
    {
        parent::__construct($auth);
        $this->table = $this->auth->getConfig('auth_rule');
    }

    /**
     * 获取规则信息
     *
     * @param array $where  where条件
     * @param array $field 查询字段
     * @return array|false
     */
    public function getInfo(array $where, array $field = ['*'])
    {
        $info = $this->where($where)->field($field)->find();
        if (!$info) {
            $this->error = '规则信息不存在';
            return false;
        }

        return $info;
    }

    /**
     * 获取所有规则信息
     *
     * @param array $option 分页参数
     * @param array $where  查询参数
     * @return array
     */
    public function getList(array $option, array $where = []): array
    {
        $page = isset($option['page']) ? intval($option['page']) : 1;
        $limit = isset($option['limit']) ? intval($option['limit']) : 10;

        $list = $this->where($where)->page($page, $limit)->select();
        $count = $this->where($where)->count('id');

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
     * @return integer|false
     */
    public function add(array $option, array $ext = [])
    {
        $check = $this->validate()->scope('rule_add')->data($option)->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }

        $info = array_merge($ext, [
            'pid'       => $option['pid'],
            'title'     => $option['title'],
            'name'      => $option['name'],
            'remark'    => $option['remark'] ?? '',
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
     * @return boolean
     */
    public function modify(array $option, array $ext = []): bool
    {
        $check = $this->validate()->scope('rule_modify')->data($option)->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
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
                    if ($v['status'] == $this->auth->getConfig('invalid_status')) {
                        $this->error = '操作失败(祖先节点存在无效节点)';
                        return false;
                    }
                }

                // 更新
                $info = array_merge($ext, [
                    'pid'       => $option['pid'],
                    'title'     => $option['title'],
                    'name'      => $option['name'],
                    'remark'    => $option['remark'] ?? '',
                    'status'    => $option['status'],
                ]);
                $save = $this->save($info, ['id' => $idx]);
                if (!$save) {
                    $this->error = '更新规则失败';
                    return false;
                }

                return true;
            } else if ($status == $this->auth->getConfig('invalid_status')) {
                // 无效，同步将所有后代节点下线
                $childrens = Tree::instance()->data($rules)->getChildrenIds($idx);

                // 更新
                $this->startTrans();
                try {
                    // 更新规则
                    $info = array_merge($ext, [
                        'pid'       => $option['pid'],
                        'title'     => $option['title'],
                        'name'      => $option['name'],
                        'remark'    => $option['remark'] ?? '',
                        'status'    => $option['status'],
                    ]);
                    $save = $this->save($info, ['id' => $idx]);
                    if (!$save) {
                        $this->rollback();
                        $this->error = '更新失败';
                        return false;
                    }

                    // 下线后代
                    if (!empty($childrens)) {
                        $offline = $this->whereIn('id', $childrens)->update(['status' => $option['status'], 'update_time' => time()]);
                        if (!$offline) {
                            $this->rollback();
                            $this->error = '修改后代权限规则失败';
                            return false;
                        }
                    }

                    // 提交事务
                    $this->commit();
                    return true;
                } catch (DbException $e) {
                    // 回滚事务
                    $this->rollback();
                    $this->error = '修改规则异常, ' . $e->getMessage();
                    return false;
                }
            }
        } else {
            // 未修改状态，直接更新
            $info = array_merge($ext, [
                'pid'       => $option['pid'],
                'title'     => $option['title'],
                'name'      => $option['name'],
                'remark'    => $option['remark'] ?? '',
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

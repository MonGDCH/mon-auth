<?php
namespace mon\auth\rbac\model;

use mon\util\Instance;
use mon\auth\rbac\model\Comm;

/**
 * 组别用户关联模型
 */
class Access extends Comm
{
    use Instance;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'mon_auth_access';

    /**
     * 获取用户所在组别
     *
     * @param integer $uid
     * @return void
     */
    public function getUserGroup(int $uid)
    {
        return $this->table('mon_auth_access a')->join('mon_auth_group b', 'a.group_id=b.id', 'left')
                    ->field('a.uid, a.group_id, b.id, b.pid, b.name, b.rules')
                    ->where('a.uid', $uid)->where('b.status', 1)->select();
    }

    /**
     * 创建组别用户关联
     *
     * @param array $option
     * @return void
     */
    public function add(array $option)
    {
        $check = $this->validate->scope('access_add')->data($option)->check();
        if ($check !== true) {
            $this->error = $check;
            return false;
        }

        if ($this->where('group_id', $option['gid'])->where('uid', $option['uid'])->get()->isEmpty()) {
            $this->error = '用户已关联，请勿重复关联';
            return false;
        }

        $save = $this->save(['uid' => $option['uid'], 'group_id' => $option['gid']]);
        if (!$save) {
            $this->error = '关联用户组别失败';
            return false;
        }

        return true;
    }

    /**
     * 修改组别用户关联
     *
     * @param array $option
     * @return void
     */
    public function modify(array $option)
    {
        $check = $this->validate->scope('access_modify')->data($option)->check();
        if ($check !== true) {
            $this->error = $check;
            return false;
        }

        $info = $this->where('uid', $option['uid'])->find();
        if (!$info) {
            $this->error = '用户未分配关联组';
            return false;
        }

        if ($info['group_id'] == $option['gid']) {
            $this->error = '用户组别重复';
            return false;
        }

        $save = $this->save(['group_id' => $option['gid']], ['uid' => $option['uid'], 'group_id' => $info['group_id']]);
        if (!$save) {
            $this->error = '更新失败';
            return false;
        }

        return true;
    }
}

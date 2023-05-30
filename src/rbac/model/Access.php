<?php

declare(strict_types=1);

namespace mon\auth\rbac\model;

use mon\util\Instance;
use mon\auth\rbac\Auth;

/**
 * 组别用户关联模型
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.1   优化代码
 */
class Access extends Base
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
        $this->table = $this->auth->getConfig('auth_group_access');
    }

    /**
     * 获取用户所在组别
     *
     * @param string|integer $uid  用户ID
     * @return array
     */
    public function getUserGroup($uid): array
    {
        return $this->table($this->table . ' a')->join($this->auth->getConfig('auth_group') . ' b', 'a.group_id=b.id')
            ->field('a.uid, a.group_id, b.id, b.pid, b.title, b.rules')
            ->where('a.uid', $uid)->where('b.status', $this->auth->getConfig('effective_status'))->select();
    }

    /**
     * 创建组别用户关联
     *
     * @param array $option 请求参数
     * @return boolean
     */
    public function bind(array $option): bool
    {
        $check = $this->validate()->scope('access_bind')->data($option)->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }

        if ($this->where('group_id', $option['gid'])->where('uid', $option['uid'])->find()) {
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
     * 解除角色组绑定
     *
     * @see 此操作为删除操作，请谨慎使用
     * @param array $option 请求参数
     * @return boolean
     */
    public function unbind(array $option): bool
    {
        $check = $this->validate()->scope('access_unbind')->data($option)->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }

        $info = $this->where('group_id', $option['gid'])->where('uid', $option['uid'])->find();
        if (!$info) {
            $this->error = '用户未绑定组别';
            return false;
        }

        $del = $this->where('group_id', $option['gid'])->where('uid', $option['uid'])->limit(1)->delete();
        if (!$del) {
            $this->error = '解除角色组绑定失败';
            return false;
        }

        return true;
    }

    /**
     * 修改组别用户关联
     *
     * @param array $option 请求参数
     * @return boolean
     */
    public function modify(array $option): bool
    {
        $check = $this->validate()->scope('access_modify')->data($option)->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }
        if ($option['new_gid'] == $option['gid']) {
            $this->error = '新组别与旧组别相同';
            return false;
        }

        $info = $this->where('uid', $option['uid'])->where('group_id', $option['gid'])->find();
        if (!$info) {
            $this->error = '用户未分配对应旧关联组';
            return false;
        }
        $exists = $this->where('uid', $option['uid'])->where('group_id', $option['new_gid'])->find();
        if ($exists) {
            $this->error = '用户已绑定新组别，请勿重复绑定';
            return false;
        }

        $save = $this->save(['group_id' => $option['new_gid']], ['uid' => $option['uid'], 'group_id' => $option['gid']]);
        if (!$save) {
            $this->error = '更新失败';
            return false;
        }

        return true;
    }
}

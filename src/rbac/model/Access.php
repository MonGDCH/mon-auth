<?php

namespace mon\auth\rbac\model;

use mon\auth\rbac\Auth;
use mon\util\Instance;
use mon\auth\rbac\Validate;
use mon\orm\Model;

/**
 * 组别用户关联模型
 */
class Access extends Model
{
    use Instance;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'auth_access';

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
        $this->table = Auth::instance()->getConfig('auth_group_access');
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
     * 获取用户所在组别
     *
     * @param integer $uid
     * @return void
     */
    public function getUserGroup($uid)
    {
        return $this->table('auth_access a')->join('auth_group b', 'a.group_id=b.id')
            ->field('a.uid, a.group_id, b.id, b.pid, b.title, b.rules')
            ->where('a.uid', $uid)->where('b.status', 1)->select();
    }

    /**
     * 创建组别用户关联
     *
     * @param array $option
     * @return void
     */
    public function bind(array $option)
    {
        $check = $this->validate->scope('access_bind')->data($option)->check();
        if ($check !== true) {
            $this->error = $check;
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
     * @param array $option
     * @return void
     */
    public function unbind(array $option)
    {
        $check = $this->validate->scope('access_unbind')->data($option)->check();
        if ($check !== true) {
            $this->error = $check;
            return false;
        }

        $info = $this->where('group_id', $option['gid'])->where('uid', $option['uid'])->find();
        if (!$info) {
            $this->error = '用户未绑定组别';
            return false;
        }

        $del = $this->where('group_id', $option['gid'])->where('uid', $option['uid'])->limit(1)->delete();
        if (!$del) {
            $this->error = '解除角色组绑定是吧';
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

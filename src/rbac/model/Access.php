<?php
namespace mon\auth\rbac\model;

use mon\util\Instance;
use mon\auth\rbac\Validate;
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
        parent::__construct();
        $this->validate = new Validate;
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

        $exists = $this->where('group_id', $option['gid'])->where('uid', $option['uid'])->find();
        if ($exists) {
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

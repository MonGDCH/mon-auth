<?php
namespace mon\auth\rbac\model;

use mon\util\Instance;
use mon\auth\rbac\Validate;
use mon\auth\rbac\model\Comm;

/**
 * 角色组模型
 */
class Group extends Comm
{
    use Instance;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'mon_auth_group';

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
     * 获取角色组信息
     *
     * @param integer $gid
     * @return void
     */
    public function getInfo(int $gid)
    {
        return $this->where('id', $gid)->get();
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

        if ($this->getInfo($option['idx'])->isEmpty()) {
            $this->error = '角色组不存在';
            return false;
        }

        $save = $this->save(['name' => $option['name'], 'pid' => $option['pid'], 'rules' => $option['rules']], ['id' => $option['idx']]);
        if (!$save) {
            $this->error = '修改角色组信息失败';
            return false;
        }

        return true;
    }
}

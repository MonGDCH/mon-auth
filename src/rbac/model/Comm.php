<?php
namespace mon\auth\rbac\model;

use mon\orm\Model;
use mon\env\Config;

/**
 * 模型基类
 */
class Comm extends Model
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->config = Config::instance()->get('mon_auth.database', []);
    }
}

<?php

declare(strict_types=1);

namespace mon\auth\Command;

use mon\orm\Db;
use mon\util\Sql;
use mon\env\Config;
use mon\console\Input;
use mon\console\Output;
use mon\console\Command;

/**
 * RBAC数据库表发布
 *
 * @author Mon <98555883@qq.com>
 * @version 1.0.0
 */
class DbRbacCommand extends Command
{
    /**
     * 指令名
     *
     * @var string
     */
    protected static $defaultName = 'dbRBAC:publish';

    /**
     * 指令描述
     *
     * @var string
     */
    protected static $defaultDescription = 'Publish the RBAC database.';

    /**
     * 指令分组
     *
     * @var string
     */
    protected static $defaultGroup = 'Auth';

    /**
     * 执行指令
     *
     * @param  Input  $in  输入实例
     * @param  Output $out 输出实例
     * @return integer  exit状态码
     */
    public function execute(Input $in, Output $out)
    {
        // 读取sql文件
        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'rbac.sql';
        $sqls = Sql::instance()->parseFile($file);
        // 表名
        $auth_group = Config::instance()->get('auth.rbac.auth_group', 'auth_group');
        $auth_access = Config::instance()->get('auth.rbac.auth_group_access', 'auth_access');
        $auth_rule = Config::instance()->get('auth.rbac.auth_rule', 'auth_rule');
        // 建表
        Db::setConfig(Config::instance()->get('database', []));
        foreach ($sqls as $i => $sql) {
            switch ($i) {
                case 0:
                    $sql = sprintf($sql, $auth_access);
                    $table = $auth_access;
                    break;
                case 1:
                    $sql = sprintf($sql, $auth_group);
                    $table = $auth_group;
                    break;
                case 2:
                    $sql = sprintf($sql, $auth_rule);
                    $table = $auth_rule;
                    break;
            }
            Db::execute($sql);
            $out->block('Create Table `' . $table . '`', 'SUCCESS');
        }
    }
}

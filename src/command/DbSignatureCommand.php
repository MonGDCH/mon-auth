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
 * Signature数据库表发布
 *
 * @author Mon <98555883@qq.com>
 * @version 1.0.0
 */
class DbSignatureCommand extends Command
{
    /**
     * 指令名
     *
     * @var string
     */
    protected static $defaultName = 'dbSignature:publish';

    /**
     * 指令描述
     *
     * @var string
     */
    protected static $defaultDescription = 'Publish the signature database.';

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
        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'api.sql';
        $content = Sql::instance()->parseFile($file);
        $content = $content[0];
        // 表名
        $table = Config::instance()->get('auth.signature.dao.construct.table', 'api_signs');
        // 建表sql
        $sql = sprintf($content, $table);
        // 建表
        Db::setConfig(Config::instance()->get('database', []));
        Db::execute($sql);
        return $out->block('Create Table `' . $table . '`', 'SUCCESS');
    }
}

<?php

declare(strict_types=1);

namespace mon\auth\Command;

use mon\auth\Install;
use mon\console\Input;
use mon\console\Output;
use mon\console\Command;

/**
 * 发布文件
 *
 * @author Mon <98555883@qq.com>
 * @version 1.0.0
 */
class AuthPublishCommand extends Command
{
    /**
     * 指令名
     *
     * @var string
     */
    protected static $defaultName = 'auth:publish';

    /**
     * 指令描述
     *
     * @var string
     */
    protected static $defaultDescription = 'Publish the vender Auth package.';

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
        Install::publish();
    }
}

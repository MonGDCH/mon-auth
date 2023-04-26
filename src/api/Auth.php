<?php

declare(strict_types=1);

namespace mon\auth\api;

use mon\util\Instance;

/**
 * API签名权限控制
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class Auth
{
    use Instance;

    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [
        // AccessToken配置
        'AccessToken'   => [
            // 权限开发
            'enable'    => true,
            // 字段映射
            'field'     => [
                // app_id字段名
                'app_id'    => 'app_id',
                // 有效时间字段名
                'expire'    => 'expire'
            ],
            // 有效时间，单位秒
            'expire'    => 3600,
            // 默认加密盐
            'salt'      => 'a!khg#-$%iu_ow1.08'
        ],
        // Signature配置
        'Signature'     => [
            // 权限开发
            'enable'    => true,
        ]
    ];
}

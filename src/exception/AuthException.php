<?php

declare(strict_types=1);

namespace mon\auth\exception;

use Exception;
use Throwable;

/**
 * 权限模型异常
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class AuthException extends Exception
{
    /**
     * 权限模块未初始化
     */
    const AUTH_INIT_ERROR = 10000;

    /**
     * openssl错误
     */
    const OPENSSL_ERROR = 10100;

    /**
     * Dao类型不支持
     */
    const DAO_NOT_SUPPORT = 40200;

    /**
     * 异常绑定数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * 重置构造方法
     *
     * @param string $message   错误信息
     * @param integer $code     错误码
     * @param Throwable|null $previous  异常
     * @param array $data       异常绑定数据
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null, array $data = [])
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取异常绑定的数据
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}

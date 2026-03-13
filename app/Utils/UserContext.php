<?php

namespace App\Utils;

use App\Models\SysUser;
use App\Models\User;

/**
 * UserContext
 * 统一获取用户信息逻辑
 */
class UserContext
{
    protected static array $map = [
        'admin'  => SysUser::class,
        'agent'  => User::class,
        'api'    => User::class,
        'index'  => User::class,
        'client' => User::class,
    ];

    /**
     * 获取当前模块对应的仓库实例
     */
    public static function instance()
    {
        $segments = request()->segments();
        $module = $segments[0] ?? 'index';

        // API routes are prefixed with /api/{module}/v{n}/...
        if ($module === 'api') {
            $module = $segments[1] ?? 'index';
        }

        $class  = static::$map[$module] ?? User::class;
        return app($class);
    }

    /** 获取完整用户信息 */
    public static function user(): array
    {
        return static::instance()->user();
    }

    /** 获取当前用户ID */
    public static function id(): ?int
    {
        return static::instance()->getUserId();
    }

    /** 获取指定字段 */
    public static function get(string|array $key, $default = null): mixed
    {
        return static::instance()->get($key, $default);
    }
}

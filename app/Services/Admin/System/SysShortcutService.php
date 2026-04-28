<?php

namespace App\Services\Admin\System;

use App\Support\Utils\Result;

class SysShortcutService
{
    public function getShortcutList(): array
    {
        $list = [
            [
                'title' => '系统用户',
                'desc'  => '管理员账号与登录信息',
                'url'   => '/admin/user',
                'icon'  => 'user',
                'color' => 'linear-gradient(135deg,#60a5fa,#2563eb)',
            ],
            [
                'title' => '角色管理',
                'desc'  => '角色新增、授权与维护',
                'url'   => '/admin/system/roles',
                'icon'  => 'auz',
                'color' => 'linear-gradient(135deg,#a78bfa,#7c3aed)',
            ],
            [
                'title' => '菜单管理',
                'desc'  => '菜单、按钮与接口权限',
                'url'   => '/admin/system/menus',
                'icon'  => 'template',
                'color' => 'linear-gradient(135deg,#34d399,#10b981)',
            ],
            [
                'title' => '字典管理',
                'desc'  => '系统参数与枚举配置',
                'url'   => '/admin/system/dicts',
                'icon'  => 'form',
                'color' => 'linear-gradient(135deg,#fbbf24,#f59e0b)',
            ],
            [
                'title' => '附件管理',
                'desc'  => '上传文件与资源管理',
                'url'   => '/admin/system/attachments',
                'icon'  => 'file',
                'color' => 'linear-gradient(135deg,#fb7185,#e11d48)',
            ],
            [
                'title' => '操作日志',
                'desc'  => '关键操作审计与追踪',
                'url'   => '/admin/system/monitor/operate-logs',
                'icon'  => 'log',
                'color' => 'linear-gradient(135deg,#22d3ee,#0891b2)',
            ],
        ];

        return Result::success([
            'data'  => $list,
            'total' => count($list),
        ], 'success');
    }
}


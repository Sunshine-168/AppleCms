<?php

/**
 * 后台管理权限配置
 */

return [

    [
        'name' => '首页',
        'icon' => 'home',

        'sub' => [

            [
                'name' => '欢迎页',
                'icon' => 'home',
                'route' => 'admin.welcome',
                'permission' => 'vod.view'
            ],

            [
                'name' => '快捷菜单',
                'icon' => 'xe640',

                'sub' => [

                    [
                        'name' => '系统快捷',
                        'route' => 'admin.quick.system',
                    ],

                    [
                        'name' => '内容快捷',

                        'sub' => [

                            // [
                            //     'name' => '视频管理',
                            //     'route' => 'admin.video.index',
                            // ],

                            [
                                'name' => '文章管理',
                                'route' => 'admin.article.index',
                            ],

                        ]

                    ]

                ]

            ]

        ]
    ],
    [
        'name' => '设置',
        'icon' => 'set',
        'sub' => [
            [
                'name' => '系统管理',
                'sub' => [
                    [
                        'name' => '管理员',
                        'url' => '/admin/user'
                    ],
                    [
                        'name' => '角色管理',
                        'url' => '/admin/system/roles'
                    ],
                    [
                        'name' => '菜单管理',
                        'url' => '/admin/system/menus'
                    ],
                    [
                        'name' => '字典管理',
                        'url' => '/admin/system/dicts'
                    ],
                    [
                        'name' => '附件管理',
                        'url' => '/admin/system/attachments'
                    ]
                ]
            ],
            [
                'name' => '数据库管理',
                'sub' => [
                    [
                        'name' => '数据库字典',
                        'url' => '/admin/system/database/dict'
                    ],
                    [
                        'name' => '数据库备份',
                        'url' => '/admin/system/database/backup'
                    ],
                    [
                        'name' => '数据库恢复',
                        'url' => '/admin/system/database/restore'
                    ],
                    [
                        'name' => 'SQL执行',
                        'url' => '/admin/system/database/sql'
                    ],
                    [
                        'name' => '数据批量替换',
                        'url' => '/admin/system/database/replace'
                    ]
                ]
            ],
            [
                'name' => '系统监控',
                'sub' => [
                    [
                        'name' => '登录日志',
                        'url' => '/admin/system/monitor/login-logs'
                    ],
                    [
                        'name' => '操作日志',
                        'url' => '/admin/system/monitor/operate-logs'
                    ],
                    [
                        'name' => '系统日志',
                        'url' => '/admin/system/monitor/system-logs'
                    ]
                ]
            ],
            [
                'name' => '系统工具',
                'sub' => [
                    [
                        'name' => '定时任务',
                        'url' => '/admin/system/tools/schedule'
                    ],
                    [
                        'name' => '缓存管理',
                        'url' => '/admin/system/tools/cache'
                    ]
                ]
            ]
        ]
    ],

];

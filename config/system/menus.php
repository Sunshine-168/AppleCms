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
                'name' => '影视管理',
                'sub' => [
                    ['name' => '站点设置', 'url' => '/admin/video/settings'],
                    ['name' => '影视列表', 'url' => '/admin/video'],
                    ['name' => '分类管理', 'url' => '/admin/video/types'],
                    ['name' => '采集资源', 'url' => '/admin/video/collects'],
                    ['name' => '标签管理', 'url' => '/admin/video/tags'],
                    ['name' => '演员管理', 'url' => '/admin/video/actors'],
                    ['name' => '专题管理', 'url' => '/admin/video/topics'],
                    ['name' => '播放器', 'url' => '/admin/video/players'],
                    ['name' => '评论管理', 'url' => '/admin/video/comments'],
                    ['name' => '报错管理', 'url' => '/admin/video/reports'],
                    ['name' => '会员管理', 'url' => '/admin/video/members'],
                    ['name' => '积分卡密', 'url' => '/admin/video/cards'],
                    ['name' => '友情链接', 'url' => '/admin/video/links'],
                    ['name' => '下载器', 'url' => '/admin/video/downloaders'],
                    ['name' => '服务器组', 'url' => '/admin/video/servers'],
                    ['name' => '播放失败', 'url' => '/admin/video/playfails'],
                    ['name' => '入库审核', 'url' => '/admin/video/audits'],
                    ['name' => '定时采集', 'url' => '/admin/video/collect_tasks'],
                    ['name' => '广告位', 'url' => '/admin/video/ads'],
                    ['name' => '留言', 'url' => '/admin/video/guestbooks'],
                    ['name' => '会员组', 'url' => '/admin/video/groups'],
                    ['name' => '会员订单', 'url' => '/admin/video/orders'],
                    ['name' => '提现', 'url' => '/admin/video/withdraws'],
                    ['name' => '站内信', 'url' => '/admin/video/pms'],
                    ['name' => '访问统计', 'url' => '/admin/video/visits'],
                    ['name' => '模板编辑', 'url' => '/admin/video/templates'],
                    ['name' => '百度推送', 'url' => '/admin/video/push'],
                    ['name' => '静态生成', 'url' => '/admin/video/make'],
                ],
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

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
                    ['name' => '无地址', 'url' => '/admin/video?empty_url=1'],
                    ['name' => '无封面', 'url' => '/admin/video?empty_pic=1'],
                    ['name' => '无简介', 'url' => '/admin/video?empty_content=1'],
                    ['name' => '无演员', 'url' => '/admin/video?no_actor=1'],
                    ['name' => '需积分', 'url' => '/admin/video?need_points=1'],
                    ['name' => '重名片', 'url' => '/admin/video?repeat=1'],
                    ['name' => '待审片', 'url' => '/admin/video?status=0'],
                    ['name' => '有分集剧情', 'url' => '/admin/video?has_plot=1'],
                    ['name' => '分类管理', 'url' => '/admin/video/types'],
                    ['name' => '采集资源', 'url' => '/admin/video/collects'],
                    ['name' => '推荐资源', 'url' => '/admin/video/unions'],
                    ['name' => '自定义规则', 'url' => '/admin/video/cj'],
                    ['name' => '采集日志', 'url' => '/admin/video/collect_logs'],
                    ['name' => '定时采集', 'url' => '/admin/video/collect_tasks'],
                    ['name' => '标签管理', 'url' => '/admin/video/tags'],
                    ['name' => '演员管理', 'url' => '/admin/video/actors'],
                    ['name' => '角色库', 'url' => '/admin/video/roles'],
                    ['name' => '专题管理', 'url' => '/admin/video/topics'],
                    ['name' => '文章管理', 'url' => '/admin/video/arts'],
                    ['name' => '网址导航', 'url' => '/admin/video/websites'],
                    ['name' => '播放器', 'url' => '/admin/video/players'],
                    ['name' => '下载器', 'url' => '/admin/video/downloaders'],
                    ['name' => '服务器组', 'url' => '/admin/video/servers'],
                    ['name' => '弹幕管理', 'url' => '/admin/video/danmaku'],
                    ['name' => '评论管理', 'url' => '/admin/video/comments'],
                    ['name' => '报错管理', 'url' => '/admin/video/reports'],
                    ['name' => '留言', 'url' => '/admin/video/guestbooks'],
                    ['name' => '友情链接', 'url' => '/admin/video/links'],
                    ['name' => '播放失败', 'url' => '/admin/video/playfails'],
                    ['name' => '入库审核', 'url' => '/admin/video/audits'],
                    ['name' => '广告位', 'url' => '/admin/video/ads'],
                    ['name' => '会员管理', 'url' => '/admin/video/members'],
                    ['name' => '会员组', 'url' => '/admin/video/groups'],
                    ['name' => '积分卡密', 'url' => '/admin/video/cards'],
                    ['name' => '会员订单', 'url' => '/admin/video/orders'],
                    ['name' => '积分流水', 'url' => '/admin/video/plogs'],
                    ['name' => '访问日志', 'url' => '/admin/video/ulogs'],
                    ['name' => '提现', 'url' => '/admin/video/withdraws'],
                    ['name' => '站内信', 'url' => '/admin/video/pms'],
                    ['name' => '绑定域名', 'url' => '/admin/video/domains'],
                    ['name' => '访问统计', 'url' => '/admin/video/visits'],
                    ['name' => '模板编辑', 'url' => '/admin/video/templates'],
                    ['name' => '标签向导', 'url' => '/admin/video/wizard'],
                    ['name' => '搜索推送', 'url' => '/admin/video/push'],
                    ['name' => '静态生成', 'url' => '/admin/video/make'],
                    ['name' => '分集剧情', 'url' => '/admin/video/plots'],
                    ['name' => '同义词', 'url' => '/admin/video/synonyms'],
                    ['name' => '扩展分类', 'url' => '/admin/video/classes'],
                    ['name' => '邀请码', 'url' => '/admin/video/invites'],
                    ['name' => '收藏', 'url' => '/admin/video/favorites'],
                    ['name' => '伪静态规则', 'url' => '/admin/video/rewrite'],
                    ['name' => '挂马扫描', 'url' => '/admin/video/safety'],
                    ['name' => '草稿', 'url' => '/admin/video?status=2'],
                    ['name' => '未通过', 'url' => '/admin/video?status=3'],
                    ['name' => '定时', 'url' => '/admin/video?status=4'],
                    ['name' => '访问风控', 'url' => '/admin/video/accesslogs'],
                    ['name' => '爬虫日志', 'url' => '/admin/video/botlogs'],
                    ['name' => '开放API', 'url' => '/admin/video/config/api'],
                    ['name' => '邮件设置', 'url' => '/admin/video/config/email'],
                    ['name' => '内容接入', 'url' => '/admin/video/config/collect'],
                    ['name' => '播放器参数', 'url' => '/admin/video/config/player'],
                    ['name' => '回收站', 'url' => '/admin/video/tools/recycle'],
                    ['name' => '远程图片', 'url' => '/admin/video/tools/images'],
                    ['name' => '内容质量', 'url' => '/admin/video/tools/quality'],
                    ['name' => '批量播放器', 'url' => '/admin/video/tools/players'],
                    ['name' => '附件清理', 'url' => '/admin/video/tools/annex'],
                    ['name' => '采集目录', 'url' => '/admin/video/tools/hub'],
                    ['name' => 'API文档', 'url' => '/admin/video/apidoc'],
                    ['name' => '搜索词', 'url' => '/admin/video/searchwords'],
                    ['name' => '幻灯片', 'url' => '/admin/video/slides'],
                    ['name' => '优惠券', 'url' => '/admin/video/coupons'],
                    ['name' => '会员通知', 'url' => '/admin/video/notifies'],
                    ['name' => '关注', 'url' => '/admin/video/follows'],
                    ['name' => '动态', 'url' => '/admin/video/dynamics'],
                    ['name' => '分享', 'url' => '/admin/video/shares'],
                    ['name' => '签到', 'url' => '/admin/video/signs'],
                    ['name' => '采集临时表', 'url' => '/admin/video/collect_temps'],
                    ['name' => '微信公众号', 'url' => '/admin/video/config/weixin'],
                    ['name' => '短信网关', 'url' => '/admin/video/config/sms'],
                    ['name' => '第三方登录', 'url' => '/admin/video/config/connect'],
                    ['name' => '后台IP白名单', 'url' => '/admin/video/config/ip'],
                    ['name' => '主题参数', 'url' => '/admin/video/config/theme'],
                    ['name' => '图片水印', 'url' => '/admin/video/config/watermark'],
                    ['name' => '统计代码', 'url' => '/admin/video/config/analytics'],
                    ['name' => 'SEO标题', 'url' => '/admin/video/config/seo'],
                    ['name' => '会员参数', 'url' => '/admin/video/config/user'],
                    ['name' => '上传限制', 'url' => '/admin/video/config/upload'],
                    ['name' => '评论留言', 'url' => '/admin/video/config/comment'],
                    ['name' => '支付参数', 'url' => '/admin/video/config/pay'],
                    ['name' => 'URL规则', 'url' => '/admin/video/config/url'],
                    ['name' => '入库接口', 'url' => '/admin/video/config/interface'],
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

<?php

/**
 * 后台管理权限配置
 * 从 maccms10/application/admin/common/auth.php 迁移
 * 
 * 注意：lang() 函数调用需要替换为实际的翻译键值
 * 在 Laravel 中使用 __() 或 trans() 函数
 */

return [
    1 => [
        'name' => '首页',
        'icon' => 'xe625',
        'sub' => [
            11 => [
                'show' => 1,
                'name' => '欢迎页',
                'controller' => 'index',
                'action' => 'welcome',
            ],
            12 => [
                'show' => 1,
                'name' => '快捷菜单',
                'controller' => 'index',
                'action' => 'quickmenu',
            ],
            1001 => [
                'show' => 0,
                'name' => '--切换布局',
                'controller' => 'index',
                'action' => 'iframe',
            ],
            1002 => [
                'show' => 0,
                'name' => '--清理缓存',
                'controller' => 'index',
                'action' => 'clear',
            ],
            1003 => [
                'show' => 0,
                'name' => '--锁屏解锁',
                'controller' => 'index',
                'action' => 'unlocked',
            ],
            1004 => [
                'show' => 0,
                'name' => '--公共下拉选择框',
                'controller' => 'index',
                'action' => 'select',
            ],
            1005 => [
                'show' => 0,
                'name' => '--文件上传',
                'controller' => 'upload',
                'action' => 'upload',
            ],
        ],
    ],
    2 => [
        'name' => '系统',
        'icon' => 'xe62e',
        'sub' => [
            21 => [
                'show' => 1,
                'name' => '基本设置',
                'controller' => 'system',
                'action' => 'config',
            ],
            210 => [
                'show' => 1,
                'name' => 'SEO设置',
                'controller' => 'system',
                'action' => 'configseo',
            ],
            211 => [
                'show' => 1,
                'name' => '用户设置',
                'controller' => 'system',
                'action' => 'configuser',
            ],
            212 => [
                'show' => 1,
                'name' => '评论设置',
                'controller' => 'system',
                'action' => 'configcomment',
            ],
            213 => [
                'show' => 1,
                'name' => '上传设置',
                'controller' => 'system',
                'action' => 'configupload',
            ],
            22 => [
                'show' => 1,
                'name' => 'URL设置',
                'controller' => 'system',
                'action' => 'configurl',
            ],
            23 => [
                'show' => 1,
                'name' => '播放设置',
                'controller' => 'system',
                'action' => 'configplay',
            ],
            24 => [
                'show' => 1,
                'name' => '采集设置',
                'controller' => 'system',
                'action' => 'configcollect',
            ],
            25 => [
                'show' => 1,
                'name' => '接口设置',
                'controller' => 'system',
                'action' => 'configinterface',
            ],
            26 => [
                'show' => 1,
                'name' => 'API设置',
                'controller' => 'system',
                'action' => 'configapi',
            ],
            27 => [
                'show' => 1,
                'name' => '第三方登录',
                'controller' => 'system',
                'action' => 'configconnect',
            ],
            28 => [
                'show' => 1,
                'name' => '支付设置',
                'controller' => 'system',
                'action' => 'configpay',
            ],
            29 => [
                'show' => 1,
                'name' => '微信设置',
                'controller' => 'system',
                'action' => 'configweixin',
            ],
            291 => [
                'show' => 1,
                'name' => '邮件设置',
                'controller' => 'system',
                'action' => 'configemail',
            ],
            292 => [
                'show' => 1,
                'name' => '短信设置',
                'controller' => 'system',
                'action' => 'configsms',
            ],
            2910 => [
                'show' => 1,
                'name' => '定时任务',
                'controller' => 'timming',
                'action' => 'index',
            ],
            2920 => [
                'show' => 1,
                'name' => '站群管理',
                'controller' => 'domain',
                'action' => 'index',
            ],
        ],
    ],
    3 => [
        'name' => '基础',
        'icon' => 'xe64b',
        'sub' => [
            31 => [
                'show' => 1,
                'name' => '分类管理',
                'controller' => 'type',
                'action' => 'index',
            ],
            32 => [
                'show' => 1,
                'name' => '专题管理',
                'controller' => 'topic',
                'action' => 'data',
            ],
            33 => [
                'show' => 1,
                'name' => '友链管理',
                'controller' => 'link',
                'action' => 'index',
            ],
            34 => [
                'show' => 1,
                'name' => '留言管理',
                'controller' => 'gbook',
                'action' => 'data',
            ],
            35 => [
                'show' => 1,
                'name' => '评论管理',
                'controller' => 'comment',
                'action' => 'data',
            ],
            36 => [
                'show' => 1,
                'name' => '附件管理',
                'controller' => 'annex',
                'action' => 'data',
            ],
        ],
    ],
    4 => [
        'name' => '视频',
        'icon' => 'xe639',
        'sub' => [
            41 => [
                'show' => 1,
                'name' => '服务器组',
                'controller' => 'vodserver',
                'action' => 'index',
            ],
            42 => [
                'show' => 1,
                'name' => '播放器',
                'controller' => 'vodplayer',
                'action' => 'index',
            ],
            43 => [
                'show' => 1,
                'name' => '下载器',
                'controller' => 'voddowner',
                'action' => 'index',
            ],
            44 => [
                'show' => 1,
                'name' => '视频管理',
                'controller' => 'vod',
                'action' => 'data',
            ],
            45 => [
                'show' => 1,
                'name' => '添加视频',
                'controller' => 'vod',
                'action' => 'info',
            ],
            495 => [
                'show' => 1,
                'name' => '演员管理',
                'controller' => 'actor',
                'action' => 'data',
            ],
            496 => [
                'show' => 1,
                'name' => '角色管理',
                'controller' => 'role',
                'action' => 'data',
            ],
        ],
    ],
    5 => [
        'name' => '文章',
        'icon' => 'xe616',
        'sub' => [
            51 => [
                'show' => 1,
                'name' => '文章管理',
                'controller' => 'art',
                'action' => 'data',
            ],
            52 => [
                'show' => 1,
                'name' => '添加文章',
                'controller' => 'art',
                'action' => 'info',
            ],
        ],
    ],
    6 => [
        'name' => '用户',
        'icon' => 'xe62c',
        'sub' => [
            61 => [
                'show' => 1,
                'name' => '管理员',
                'controller' => 'admin',
                'action' => 'index',
            ],
            62 => [
                'show' => 1,
                'name' => '会员组',
                'controller' => 'group',
                'action' => 'index',
            ],
            63 => [
                'show' => 1,
                'name' => '会员管理',
                'controller' => 'user',
                'action' => 'data',
            ],
            64 => [
                'show' => 1,
                'name' => '充值卡',
                'controller' => 'card',
                'action' => 'index',
            ],
            65 => [
                'show' => 1,
                'name' => '订单管理',
                'controller' => 'order',
                'action' => 'index',
            ],
            66 => [
                'show' => 1,
                'name' => '访问日志',
                'controller' => 'ulog',
                'action' => 'index',
            ],
            67 => [
                'show' => 1,
                'name' => '积分日志',
                'controller' => 'plog',
                'action' => 'index',
            ],
            68 => [
                'show' => 1,
                'name' => '提现管理',
                'controller' => 'cash',
                'action' => 'index',
            ],
        ],
    ],
    7 => [
        'name' => '模板',
        'icon' => 'xe72d',
        'sub' => [
            71 => [
                'show' => 1,
                'name' => '模板管理',
                'controller' => 'template',
                'action' => 'index',
            ],
            72 => [
                'show' => 1,
                'name' => '广告管理',
                'controller' => 'template',
                'action' => 'ads',
            ],
            73 => [
                'show' => 1,
                'name' => '模板向导',
                'controller' => 'template',
                'action' => 'wizard',
            ],
        ],
    ],
    8 => [
        'name' => '生成',
        'icon' => 'xe63e',
        'sub' => [
            81 => [
                'show' => 1,
                'name' => '生成选项',
                'controller' => 'make',
                'action' => 'opt',
            ],
            82 => [
                'show' => 1,
                'name' => '生成首页',
                'controller' => 'make',
                'action' => 'index',
            ],
            83 => [
                'show' => 1,
                'name' => '生成地图',
                'controller' => 'make',
                'action' => 'map',
            ],
        ],
    ],
    9 => [
        'name' => '采集',
        'icon' => 'xe727',
        'sub' => [
            93 => [
                'show' => 1,
                'name' => '自定义资源',
                'controller' => 'collect',
                'action' => 'index',
            ],
            94 => [
                'show' => 1,
                'name' => '自定义规则',
                'controller' => 'cj',
                'action' => 'index',
            ],
        ],
    ],
    10 => [
        'name' => '数据库',
        'icon' => 'xe621',
        'sub' => [
            101 => [
                'show' => 1,
                'name' => '数据库管理',
                'controller' => 'database',
                'action' => 'index',
            ],
            102 => [
                'show' => 1,
                'name' => 'SQL执行',
                'controller' => 'database',
                'action' => 'sql',
            ],
            103 => [
                'show' => 1,
                'name' => '数据替换',
                'controller' => 'database',
                'action' => 'rep',
            ],
        ],
    ],
    11 => [
        'name' => '应用',
        'icon' => 'xe621',
        'sub' => [
            111 => [
                'show' => 1,
                'name' => '应用插件',
                'controller' => 'addon',
                'action' => 'index',
            ],
            112 => [
                'show' => 1,
                'name' => 'URL推送',
                'controller' => 'urlsend',
                'action' => 'index',
            ],
            113 => [
                'show' => 1,
                'name' => '文件安全',
                'controller' => 'safety',
                'action' => 'file',
            ],
            114 => [
                'show' => 1,
                'name' => '数据安全',
                'controller' => 'safety',
                'action' => 'data',
            ],
        ],
    ],
    12 => [
        'name' => '网址',
        'icon' => 'xe616',
        'sub' => [
            121 => [
                'show' => 1,
                'name' => '网址管理',
                'controller' => 'website',
                'action' => 'data',
            ],
            122 => [
                'show' => 1,
                'name' => '添加网址',
                'controller' => 'website',
                'action' => 'info',
            ],
        ],
    ],
    13 => [
        'name' => '漫画',
        'icon' => 'xe616',
        'sub' => [
            131 => [
                'show' => 1,
                'name' => '漫画管理',
                'controller' => 'manga',
                'action' => 'data',
            ],
            132 => [
                'show' => 1,
                'name' => '添加漫画',
                'controller' => 'manga',
                'action' => 'info',
            ],
        ],
    ],
];

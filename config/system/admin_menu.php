<?php

/**
 * 后台管理权限配置
 * 从 maccms10/application/admin/common/auth.php 迁移
 *
 * 注意：lang() 函数调用需要替换为实际的翻译键值
 * 在 Laravel 中使用 __() 或 trans() 函数
 */

return [

    [
        'name' => '首页',
        'icon' => 'xe625',

        'sub' => [

            [
                'name' => '欢迎页',
                'icon' => 'xe63c',
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

                            [
                                'name' => '视频管理',
                                'route' => 'admin.vod.index',
                            ],

                            [
                                'name' => '文章管理',
                                'route' => 'admin.article.index',
                            ],

                        ]

                    ]

                ]

            ]

        ]

    ]

];

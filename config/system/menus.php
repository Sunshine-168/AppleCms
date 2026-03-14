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

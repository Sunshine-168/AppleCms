<?php

return [
    'theme' => env('VIDEO_THEME', 'default'),

    'site' => [
        'title' => env('VIDEO_SITE_TITLE', 'LaraVideo'),
        'keyword' => env('VIDEO_SITE_KEYWORDS', ''),
        'description' => env('VIDEO_SITE_DESCRIPTION', ''),
    ],

    'prefer_file_menus' => (bool) env('VIDEO_PREFER_FILE_MENUS', true),

    'html_cache' => [
        'enabled' => (bool) env('VIDEO_HTML_CACHE', false),
        'ttl' => (int) env('VIDEO_HTML_CACHE_TTL', 3600),
    ],

    'disk_html' => [
        'enabled' => (bool) env('VIDEO_DISK_HTML', false),
        'path' => env('VIDEO_DISK_HTML_PATH', 'html'),
        'root' => env('VIDEO_DISK_HTML_ROOT', ''),
    ],

    'rewrite' => [
        'mode' => env('VIDEO_REWRITE', 'laravel'),
        'suffix' => env('VIDEO_REWRITE_SUFFIX', '.html'),
    ],

    'per_page' => 24,

    'orders' => [
        'time' => '最新',
        'hits' => '最热',
        'score' => '评分',
        'year' => '年份',
    ],
];

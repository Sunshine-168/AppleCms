<?php

return [

    'paths' => [
        resource_path('views'),
    ],

    /*
     * 不用 realpath()。目录还不存在时 realpath 会返回 false，
     * package:discover 里注册 Blade 指令就会报 “valid cache path”。
     */
    'compiled' => env('VIEW_COMPILED_PATH', storage_path('framework/views')),

];

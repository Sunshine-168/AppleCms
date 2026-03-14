<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\System\SysUser;

Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
])->post('admin/login', [SysUser::class, 'login']);

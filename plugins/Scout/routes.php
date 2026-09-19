<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminIpAllow;
use App\Http\Middleware\AdminOperateLog;
use App\Http\Middleware\AdminPermission;
use Illuminate\Support\Facades\Route;
use Plugins\Scout\Http\Controllers\ScoutAdminController;

Route::middleware([
    'web',
    AdminIpAllow::class,
    AdminOperateLog::class,
    AdminAuth::class,
    AdminPermission::class,
])->prefix('admin')->group(function () {
    Route::post('/video/scout/sync', [ScoutAdminController::class, 'sync']);
});

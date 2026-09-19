<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminIpAllow;
use App\Http\Middleware\AdminOperateLog;
use App\Http\Middleware\AdminPermission;
use Illuminate\Support\Facades\Route;
use Plugins\Live\Http\Controllers\LiveCategoryAdminController;
use Plugins\Live\Http\Controllers\LiveChannelAdminController;
use Plugins\Live\Http\Controllers\LiveController;

Route::middleware('web')->group(function () {
    Route::get('/live', [LiveController::class, 'index']);
    Route::get('/live/{id}', [LiveController::class, 'show'])->whereNumber('id');
});

Route::middleware([
    'web',
    AdminIpAllow::class,
    AdminOperateLog::class,
    AdminAuth::class,
    AdminPermission::class,
])->prefix('admin')->group(function () {
    Route::get('/video/live-channels/create', [LiveChannelAdminController::class, 'create']);
    Route::get('/video/live-channels/{id}/edit', [LiveChannelAdminController::class, 'edit'])->whereNumber('id');
    Route::get('/video/live-categories/create', [LiveCategoryAdminController::class, 'create']);
    Route::get('/video/live-categories/{id}/edit', [LiveCategoryAdminController::class, 'edit'])->whereNumber('id');
});

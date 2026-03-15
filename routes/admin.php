<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\System\SysUser;
use App\Http\Controllers\Admin\Index;

Route::middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
])->withoutMiddleware([
    VerifyCsrfToken::class,
])->post('api/admin/login', [SysUser::class, 'login']);

Route::prefix('admin')->group(function () {
    Route::get('/', [Index::class, 'index']); // 后台首页
    Route::get('/captcha', [Index::class, 'captcha']);   // 输出验证码图片

    Route::get('/login', [SysUser::class, 'showLogin']); // 显示登录页
    Route::post('/login', [SysUser::class, 'login']);    // 表单提交处理登录
    // 系统用户
    Route::get('/user', [SysUser::class,'index']);// 显示系统用户列表页
    Route::get('/user/list', [SysUser::class,'getSystemUserLists']);// 获取系统用户列表页
    Route::get('/user/info', [SysUser::class, 'getSystemUserInfo']);// 获取系统用户详情
    Route::post('/user/add', [SysUser::class, 'addSystemUser']);// 添加用户
    Route::post('/user/update', [SysUser::class, 'updateSystemUser']);// 更新用户
    Route::post('/user/delete', [SysUser::class, 'deleteSystemUser']);// 删除用户
    Route::get('/user/login/list', [SysUser::class, 'getSystemUserLoginLists']);// 获取系统用户登录日志列表
    Route::get('/system/monitor/login-logs', [SysUser::class, 'showSystemUserLoginLogs']);// 显示系统用户登录日志页
    Route::get('/system/monitor/login-logs/list', [SysUser::class, 'getSystemUserLoginLists']);// 获取系统用户登录日志列表
    



});

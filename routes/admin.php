<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\System\SysUser;
use App\Http\Controllers\Admin\Index;

Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
])->withoutMiddleware([
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
])->post('api/admin/login', [SysUser::class, 'login']);

Route::prefix('admin')->group(function () {
    Route::get('/', [Index::class, 'index']); // 后台首页
    Route::get('/captcha', [Index::class, 'captcha']);   // 输出验证码图片
    
    Route::get('/login', [SysUser::class, 'showLogin']); // 显示登录页
    Route::post('/login', [SysUser::class, 'login']);    // 表单提交处理登录

    Route::get('/sysuser', [SysUser::class,'index']);// 显示系统用户列表页
    Route::get('/sysuser/list', [SysUser::class,'getSystemUserLists']);// 获取系统用户列表页

    

});

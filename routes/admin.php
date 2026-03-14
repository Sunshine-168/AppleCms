<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\System\SysUser;
use App\Http\Controllers\Admin\Index;

Route::prefix('admin')->group(function () {
    Route::get('/', [Index::class, 'index']); // 后台首页
    Route::get('/captcha', [Index::class, 'captcha']);   // 输出验证码图片
    
    Route::get('/login', [SysUser::class, 'showLogin']); // 显示登录页
    Route::post('/login', [SysUser::class, 'login']);    // 表单提交处理登录
    

});

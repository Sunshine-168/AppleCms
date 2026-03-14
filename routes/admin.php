<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\System\SysUser;

Route::prefix('admin')->group(function () {

    Route::get('/login', [SysUser::class, 'showLogin']); // 显示登录页
    Route::post('/login', [SysUser::class, 'login']);    // 表单提交处理登录

});

<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\System\SysDatabase;
use App\Http\Controllers\Admin\System\SysUser;
use App\Http\Controllers\Admin\Index;
use App\Http\Middleware\AdminOperateLog;

Route::middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    AdminOperateLog::class,
])->withoutMiddleware([
    VerifyCsrfToken::class,
])->post('api/admin/login', [SysUser::class, 'login']);

Route::middleware([AdminOperateLog::class])->prefix('admin')->group(function () {
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
  
    // 系统监控
    Route::prefix('/system/monitor')->controller(SysUser::class)->group(function () {
        Route::get('/login-logs', 'showSystemUserLoginLogs');// 显示系统用户登录日志页
        Route::get('/login-logs/list', 'getSystemUserLoginLists');// 获取系统用户登录日志列表
        Route::get('/operate-logs', 'showSystemOperateLogs');// 显示系统用户操作日志页
        Route::get('/operate-logs/list', 'getSystemOperateLogLists');// 获取系统用户操作日志列表
        Route::get('/system-logs', 'showSystemSystemLogs');// 显示系统日志页
        Route::get('/system-logs/list', 'getSystemSystemLogLists');// 获取系统日志列表
    });
    // 数据库
    Route::prefix('/system/database')->controller(SysDatabase::class)->group(function () {
        Route::get('/dict', 'showDatabaseDict');// 显示数据库字典页
        Route::get('/dict/tables', 'getDatabaseTables');// 获取数据库表列表
        Route::get('/dict/columns', 'getDatabaseColumns');// 获取数据库表字段列表

        Route::get('/backup', 'showDatabaseBackup');// 显示数据库备份页
        Route::get('/backup/run', 'showDatabaseBackup');// 兼容直接访问执行地址
        Route::post('/backup/run', 'runDatabaseBackup');// 执行数据库备份
        Route::get('/backup/files', 'getDatabaseBackupFiles');// 获取备份文件列表
        Route::get('/backup/download', 'downloadDatabaseBackupFile');// 下载备份文件
        Route::post('/backup/delete', 'deleteDatabaseBackupFile');// 删除备份文件

        Route::get('/restore', 'showDatabaseRestore');// 显示数据库恢复页
        Route::post('/restore/run', 'runDatabaseRestore');// 执行数据库恢复
        Route::get('/restore/files', 'getDatabaseRestoreFiles');// 获取可恢复的备份文件列表
    });
    



});

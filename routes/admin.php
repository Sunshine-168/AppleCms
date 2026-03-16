<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\System\SysDatabase;
use App\Http\Controllers\Admin\System\SysCache;
use App\Http\Controllers\Admin\System\SysSchedule;
use App\Http\Controllers\Admin\System\SysRole;
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

    // 角色管理
    Route::get('/system/roles', [SysRole::class, 'index']);// 显示角色管理页
    Route::get('/system/roles/list', [SysRole::class, 'getRoleLists']);// 获取角色列表
    Route::post('/system/roles/add', [SysRole::class, 'addRole']);// 新增角色
    Route::post('/system/roles/update', [SysRole::class, 'updateRole']);// 更新角色
    Route::post('/system/roles/delete', [SysRole::class, 'deleteRole']);// 删除角色
    Route::post('/system/roles/perms/set', [SysRole::class, 'setRolePerms']);// 设置角色权限
    Route::get('/system/roles/perms/ids', [SysRole::class, 'getRolePermIds']);// 获取角色权限ID列表

    Route::prefix('/set/user')->controller(SysUser::class)->group(function () {
        Route::get('/password', 'showChangePassword');// 显示修改密码页
        Route::get('/password.html', 'showChangePassword');// 兼容 lay-href 默认地址
        Route::post('/password', 'changePassword');// 修改密码
    });
  
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

        Route::get('/sql', 'showDatabaseSql');// 显示SQL执行页
        Route::post('/sql/run', 'runDatabaseSql');// 执行SQL

        Route::get('/replace', 'showDatabaseReplace');// 显示数据批量替换页
        Route::post('/replace/run', 'runDatabaseReplace');// 执行数据批量替换
    });
    // 系统工具
    Route::prefix('/system/tools')->controller(SysSchedule::class)->group(function () {
        Route::get('/schedule', 'showSchedule');// 显示定时任务页
        Route::get('/schedule/list', 'getScheduleList');// 获取定时任务列表
        Route::post('/schedule/save', 'saveSchedule');// 保存定时任务
        Route::post('/schedule/delete', 'deleteSchedule');// 删除定时任务
        Route::post('/schedule/status', 'updateScheduleStatus');// 更新定时任务状态
        Route::post('/schedule/run', 'runScheduleOnce');// 立即执行定时任务
    });
    // 缓存管理
    Route::prefix('/system/tools')->controller(SysCache::class)->group(function () {
        Route::get('/cache', 'showCache');// 显示缓存管理页
        Route::get('/cache/info', 'getCacheInfo');// 获取缓存信息
        Route::post('/cache/flush', 'flushCache');// 清空缓存
        Route::post('/cache/run', 'runCacheCommand');// 执行缓存相关命令
    });
    



});

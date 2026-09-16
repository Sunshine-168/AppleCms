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
use App\Http\Controllers\Admin\System\SysDict;
use App\Http\Controllers\Admin\System\SysFile;
use App\Http\Controllers\Admin\System\SysShortcut;
use App\Http\Controllers\Admin\System\SysUser;
use App\Http\Controllers\Admin\System\SysPerm;
use App\Http\Controllers\Admin\Video\Video;
use App\Http\Controllers\Admin\Video\Collect;
use App\Http\Controllers\Admin\Video\SiteModule;
use App\Http\Controllers\Admin\Video\SiteOps;
use App\Http\Controllers\Admin\Video\SiteSetting;
use App\Http\Controllers\Admin\Video\SiteTools;
use App\Http\Controllers\Admin\Index;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\StatController;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminIpAllow;
use App\Http\Middleware\AdminOperateLog;
use App\Http\Middleware\AdminPermission;

Route::middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    AdminIpAllow::class,
    AdminOperateLog::class,
])->withoutMiddleware([
    VerifyCsrfToken::class,
])->post('api/admin/login', [SysUser::class, 'login']);

Route::middleware([AdminIpAllow::class, AdminOperateLog::class, AdminAuth::class, AdminPermission::class])->prefix('admin')->group(function () {
    Route::get('/', [Index::class, 'index']); // 后台首页
    Route::get('/captcha', [Index::class, 'captcha']);   // 加减法验证码
    Route::get('/welcome', [Index::class, 'welcome'])->name('admin.welcome');// 欢迎页
    Route::get('/welcome/stats', [Index::class, 'welcomeStats'])->name('admin.welcome.stats');// 欢迎页统计数据
    Route::get('/more', [Index::class, 'more'])->name('admin.more');
    Route::get('/plugins', [PluginController::class, 'index'])->name('admin.plugins');
    Route::get('/plugins/{id}', [PluginController::class, 'show'])->where('id', '[a-z][a-z0-9_]*')->name('admin.plugins.show');
    Route::post('/plugins/{id}/toggle', [PluginController::class, 'toggle'])->where('id', '[a-z][a-z0-9_]*')->name('admin.plugins.toggle');
    Route::post('/ui-locale', [Index::class, 'switchUi'])->name('admin.ui-locale');
    Route::get('/stats', [StatController::class, 'index'])->name('admin.stats.index');
    Route::get('/stats/export', [StatController::class, 'export'])->name('admin.stats.export');
    Route::get('/stats/spiders', [StatController::class, 'spiders'])->name('admin.stats.spiders');
    Route::get('/stats/spiders/export', [StatController::class, 'exportSpiders'])->name('admin.stats.spiders.export');
    Route::get('/stats/logs', [StatController::class, 'logs'])->name('admin.stats.logs');
    Route::get('/stats/logs/export', [StatController::class, 'exportLogs'])->name('admin.stats.logs.export');

    Route::get('/login', [SysUser::class, 'showLogin']); // 显示登录页
    Route::post('/login', [SysUser::class, 'login']);    // 表单提交处理登录
    Route::match(['get', 'post'], '/logout', [SysUser::class, 'logout']); // 退出登录
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
    Route::get('/system/roles/options', [SysRole::class, 'getRoleOptions']);// 角色下拉选项
    Route::post('/system/roles/add', [SysRole::class, 'addRole']);// 新增角色
    Route::post('/system/roles/update', [SysRole::class, 'updateRole']);// 更新角色
    Route::post('/system/roles/delete', [SysRole::class, 'deleteRole']);// 删除角色
    Route::post('/system/roles/perms/set', [SysRole::class, 'setRolePerms']);// 设置角色权限
    Route::get('/system/roles/perms/ids', [SysRole::class, 'getRolePermIds']);// 获取角色权限ID列表

    Route::get('/system/perms/tree', [SysPerm::class, 'getPermTree']);// 权限树（角色授权用）

    // 菜单管理（权限）
    Route::get('/system/menus', [SysPerm::class, 'index']);// 显示菜单管理页
    Route::get('/system/menus/list', [SysPerm::class, 'getMenuLists']);// 获取菜单列表（树形展开）
    Route::get('/system/menus/parents', [SysPerm::class, 'getMenuParentOptions']);// 父级菜单下拉
    Route::post('/system/menus/add', [SysPerm::class, 'addMenu']);// 新增菜单/权限
    Route::post('/system/menus/update', [SysPerm::class, 'updateMenu']);// 更新菜单/权限
    Route::post('/system/menus/delete', [SysPerm::class, 'deleteMenu']);// 删除菜单/权限（级联）

    // 字段管理（系统字典）
    Route::get('/system/dicts', [SysDict::class, 'index']);// 显示系统字典页
    Route::get('/system/dicts/list', [SysDict::class, 'getSysLists']);// 获取系统字典列表
    Route::post('/system/dicts/add', [SysDict::class, 'addSysSet']);// 添加系统字典
    Route::post('/system/dicts/update', [SysDict::class, 'updateSysSet']);// 更新系统字典
    Route::post('/system/dicts/delete', [SysDict::class, 'deleteSysSet']);// 删除系统字典
    Route::post('/system/dicts/state', [SysDict::class, 'updateState']);// 更新系统字典状态
   
    // 快捷菜单管理
    Route::get('/system/shortcut', [SysShortcut::class, 'index'])->name('admin.quick.system');// 显示快捷菜单管理页
    Route::get('/system/shortcut/list', [SysShortcut::class, 'getShortcutList'])->name('admin.quick.system.list');// 获取快捷菜单列表
    
    // 视频管理
    Route::get('/video', [Video::class, 'index'])->name('admin.video.index');
    Route::get('/video/index', [Video::class, 'index']);// 视频管理首页
    Route::get('/video/list', [Video::class, 'getVideoLists'])->name('admin.video.list');// 获取视频列表
    Route::get('/video/info', [Video::class, 'getVideoInfo'])->name('admin.video.info');// 获取视频详情
    Route::post('/video/save', [Video::class, 'saveVideo'])->name('admin.video.save');// 保存视频
    Route::post('/video/delete', [Video::class, 'deleteVideo'])->name('admin.video.delete');// 删除视频
    Route::post('/video/batch', [Video::class, 'batchVideo'])->name('admin.video.batch');
    Route::get('/video/types/options', [Video::class, 'getTypeOptions'])->name('admin.video.types.options');// 视频类型下拉选项
    Route::get('/video/collect/options', [Video::class, 'getCollectSourceOptions'])->name('admin.video.collect.options');// 视频采集源下拉选项
    
    // 视频类型管理
    Route::get('/video/types', [Video::class, 'showTypes'])->name('admin.video.types');// 显示视频类型管理页
    Route::get('/video/types/list', [Video::class, 'getTypeLists'])->name('admin.video.types.list');// 获取视频类型列表
    Route::post('/video/types/save', [Video::class, 'saveType'])->name('admin.video.types.save');// 保存视频类型
    Route::post('/video/types/delete', [Video::class, 'deleteType'])->name('admin.video.types.delete');// 删除视频类型
    Route::post('/video/types/batch', [Video::class, 'batchTypes'])->name('admin.video.types.batch');

    // 视频采集源管理
    Route::get('/video/collects', [Video::class, 'showCollectSources'])->name('admin.video.collects');// 显示视频采集源管理页
    Route::get('/video/collects/list', [Video::class, 'getCollectSourceLists'])->name('admin.video.collects.list');// 获取视频采集源列表
    Route::post('/video/collects/save', [Video::class, 'saveCollectSource'])->name('admin.video.collects.save');// 保存视频采集源
    Route::post('/video/collects/delete', [Video::class, 'deleteCollectSource'])->name('admin.video.collects.delete');// 删除视频采集源
    Route::get('/video/collects/classes', [Collect::class, 'classes'])->name('admin.video.collects.classes');
    Route::post('/video/collects/bind', [Collect::class, 'bind'])->name('admin.video.collects.bind');
    Route::post('/video/collects/run', [Collect::class, 'run'])->name('admin.video.collects.run');
    Route::post('/video/collects/resume', [Collect::class, 'resume']);
    Route::post('/video/collects/retry', [Collect::class, 'retry']);
    Route::post('/video/collects/suggest', [Collect::class, 'suggestBind']);
    Route::post('/video/sources/disable', [Video::class, 'disableSource']);

    // 视频标签管理
    Route::get('/video/tags', [Video::class, 'showTags'])->name('admin.video.tags');// 显示视频标签管理页
    Route::get('/video/tags/list', [Video::class, 'getTagLists'])->name('admin.video.tags.list');// 获取视频标签列表
    Route::post('/video/tags/save', [Video::class, 'saveTag'])->name('admin.video.tags.save');
    Route::post('/video/tags/delete', [Video::class, 'deleteTag'])->name('admin.video.tags.delete');
    Route::post('/video/tags/batch', [Video::class, 'batchTags'])->name('admin.video.tags.batch');
    // 视频演员管理
    Route::get('/video/actors', [Video::class, 'showActors'])->name('admin.video.actors');// 显示视频演员管理页
    Route::get('/video/actors/list', [Video::class, 'getActorLists'])->name('admin.video.actors.list');// 获取视频演员列表
    Route::post('/video/actors/save', [Video::class, 'saveActor'])->name('admin.video.actors.save');
    Route::post('/video/actors/delete', [Video::class, 'deleteActor'])->name('admin.video.actors.delete');
    Route::post('/video/actors/batch', [Video::class, 'batchActors'])->name('admin.video.actors.batch'); 

    // 视频源管理
    Route::get('/video/sources', [Video::class, 'showSources'])->name('admin.video.sources');// 显示视频源管理页
    Route::get('/video/sources/list', [Video::class, 'getSourceLists'])->name('admin.video.sources.list');// 获取视频源列表
    Route::post('/video/sources/save', [Video::class, 'saveSource'])->name('admin.video.sources.save');// 保存视频源
    Route::post('/video/sources/delete', [Video::class, 'deleteSource'])->name('admin.video.sources.delete');// 删除视频源
    
    // 视频集管理
    Route::get('/video/episodes', [Video::class, 'showEpisodes'])->name('admin.video.episodes');// 显示视频集管理页
    Route::get('/video/episodes/list', [Video::class, 'getEpisodeLists'])->name('admin.video.episodes.list');// 获取视频集列表
    Route::post('/video/episodes/save', [Video::class, 'saveEpisode'])->name('admin.video.episodes.save');// 保存视频集
    Route::post('/video/episodes/delete', [Video::class, 'deleteEpisode'])->name('admin.video.episodes.delete');// 删除视频集

    Route::get('/video/settings', [SiteSetting::class, 'index']);
    Route::post('/video/settings', [SiteSetting::class, 'save']);
    Route::post('/video/settings/test-mail', [SiteSetting::class, 'testMail']);
    Route::get('/video/config/email', [SiteSetting::class, 'configEmail']);
    Route::get('/video/config/{page}', [SiteSetting::class, 'configPage'])->where('page', '[a-z]+');
    Route::get('/video/tools/{tool}', [SiteTools::class, 'page'])->whereIn('tool', ['images', 'quality', 'players', 'annex', 'recycle', 'hub']);
    Route::post('/video/tools/{tool}/run', [SiteTools::class, 'run'])->whereIn('tool', ['images', 'quality', 'players', 'annex', 'recycle', 'hub']);
    Route::get('/video/apidoc', [SiteTools::class, 'apidoc']);
    Route::post('/video/collect_temps/promote', [SiteTools::class, 'promoteTemp']);
    Route::post('/video/cards/generate', [SiteModule::class, 'generateCards']);
    Route::post('/video/invites/generate', [SiteModule::class, 'generateInvites']);
    Route::post('/video/collect_tasks/run', [SiteModule::class, 'runCollectTask']);
    Route::get('/video/topics/{id}/videos', [SiteModule::class, 'topicVideos'])->whereNumber('id');
    Route::post('/video/topics/{id}/videos', [SiteModule::class, 'saveTopicVideos'])->whereNumber('id');

    Route::get('/video/templates', [SiteOps::class, 'templates']);
    Route::get('/video/templates/read', [SiteOps::class, 'templateRead']);
    Route::post('/video/templates/save', [SiteOps::class, 'templateSave']);
    Route::post('/video/templates/backup', [SiteOps::class, 'templateBackup']);
    Route::post('/video/templates/rollback', [SiteOps::class, 'templateRollback']);
    Route::get('/video/visits', fn () => redirect()->route('admin.stats.index'));
    Route::get('/video/push', [SiteOps::class, 'push']);
    Route::post('/video/push/run', [SiteOps::class, 'pushRun']);
    Route::get('/video/make', [SiteOps::class, 'make']);
    Route::post('/video/make/run', [SiteOps::class, 'makeRun']);
    Route::post('/video/make/map', [SiteOps::class, 'makeMap']);
    Route::post('/video/playfails/offline', [SiteOps::class, 'disableFailSource']);
    Route::get('/video/wizard', [SiteOps::class, 'wizard']);
    Route::post('/video/hits-reset', [SiteOps::class, 'hitsReset']);
    Route::get('/video/rewrite', [SiteOps::class, 'rewrite']);
    Route::get('/video/safety', [SiteOps::class, 'safety']);
    Route::post('/video/safety/scan', [SiteOps::class, 'malwareScan']);
    Route::post('/video/batch-replace-url', [SiteOps::class, 'batchReplaceUrl']);
    Route::post('/video/collect-due', [SiteOps::class, 'collectDue']);

    Route::get('/video/{module}', [SiteModule::class, 'index'])->where('module', '[a-z][a-z0-9_]*');
    Route::get('/video/{module}/list', [SiteModule::class, 'list'])->where('module', '[a-z][a-z0-9_]*');
    Route::post('/video/{module}/save', [SiteModule::class, 'save'])->where('module', '[a-z][a-z0-9_]*');
    Route::post('/video/{module}/delete', [SiteModule::class, 'delete'])->where('module', '[a-z][a-z0-9_]*');
    Route::post('/video/{module}/batch', [SiteModule::class, 'batch'])->where('module', '[a-z][a-z0-9_]*');
    
    // 附件管理
    Route::get('/system/attachments', [SysFile::class, 'index']);// 显示附件管理页
    Route::get('/system/attachments/list', [SysFile::class, 'getLists']);// 获取附件列表
    Route::get('/system/attachments/open', [SysFile::class, 'open']);// 打开附件
    Route::post('/system/attachments/upload', [SysFile::class, 'upload']);// 上传附件
    Route::post('/system/attachments/delete', [SysFile::class, 'delete']);// 删除附件

    // 修改系统用户密码
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

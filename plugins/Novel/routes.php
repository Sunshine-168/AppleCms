<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminIpAllow;
use App\Http\Middleware\AdminOperateLog;
use App\Http\Middleware\AdminPermission;
use Illuminate\Support\Facades\Route;
use Plugins\Novel\Http\Controllers\NovelAuthorAdminController;
use Plugins\Novel\Http\Controllers\NovelChapterAdminController;
use Plugins\Novel\Http\Controllers\NovelController;
use Plugins\Novel\Http\Controllers\NovelTagAdminController;
use Plugins\Novel\Http\Controllers\NovelWorkAdminController;

Route::middleware('web')->group(function () {
    Route::get('/novel', [NovelController::class, 'index']);
    Route::get('/novel/shelf', [NovelController::class, 'shelf']);
    Route::get('/novel/history', [NovelController::class, 'history']);
    Route::post('/novel/{id}/comment', [NovelController::class, 'comment'])->whereNumber('id');
    Route::post('/novel/{id}/favor', [NovelController::class, 'favor'])->whereNumber('id');
    Route::get('/novel/{id}', [NovelController::class, 'show'])->whereNumber('id');
    Route::get('/novel/{id}/{chapter}', [NovelController::class, 'read'])->whereNumber('id')->whereNumber('chapter');
});

Route::middleware([
    'web',
    AdminIpAllow::class,
    AdminOperateLog::class,
    AdminAuth::class,
    AdminPermission::class,
])->prefix('admin')->group(function () {
    Route::get('/video/novels/create', [NovelWorkAdminController::class, 'create']);
    Route::get('/video/novels/{id}/edit', [NovelWorkAdminController::class, 'edit'])->whereNumber('id');
    Route::get('/video/novel-chapters/create', [NovelChapterAdminController::class, 'create']);
    Route::get('/video/novel-chapters/{id}/edit', [NovelChapterAdminController::class, 'edit'])->whereNumber('id');

    Route::get('/video/novel-tags', [NovelTagAdminController::class, 'index']);
    Route::get('/video/novel-tags/create', [NovelTagAdminController::class, 'create']);
    Route::get('/video/novel-tags/{id}/edit', [NovelTagAdminController::class, 'edit'])->whereNumber('id');
    Route::get('/video/novel-tags/list', [NovelTagAdminController::class, 'list']);
    Route::post('/video/novel-tags/save', [NovelTagAdminController::class, 'save']);
    Route::post('/video/novel-tags/delete', [NovelTagAdminController::class, 'delete']);
    Route::post('/video/novel-tags/batch', [NovelTagAdminController::class, 'batch']);

    Route::get('/video/novel-authors', [NovelAuthorAdminController::class, 'index']);
    Route::get('/video/novel-authors/create', [NovelAuthorAdminController::class, 'create']);
    Route::get('/video/novel-authors/{id}/edit', [NovelAuthorAdminController::class, 'edit'])->whereNumber('id');
    Route::get('/video/novel-authors/list', [NovelAuthorAdminController::class, 'list']);
    Route::post('/video/novel-authors/save', [NovelAuthorAdminController::class, 'save']);
    Route::post('/video/novel-authors/delete', [NovelAuthorAdminController::class, 'delete']);
    Route::post('/video/novel-authors/batch', [NovelAuthorAdminController::class, 'batch']);
});

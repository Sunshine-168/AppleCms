<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminIpAllow;
use App\Http\Middleware\AdminOperateLog;
use App\Http\Middleware\AdminPermission;
use Illuminate\Support\Facades\Route;
use Plugins\Manga\Http\Controllers\MangaController;
use Plugins\Manga\Http\Controllers\MangaTagAdminController;
use Plugins\Manga\Http\Controllers\MangaTypeAdminController;

Route::middleware('web')->group(function () {
    Route::get('/manga', [MangaController::class, 'index']);
    Route::get('/manga/rank', [MangaController::class, 'rank']);
    Route::get('/manga/update', [MangaController::class, 'updates']);
    Route::get('/manga/shelf', [MangaController::class, 'shelf']);
    Route::get('/manga/history', [MangaController::class, 'history']);
    Route::post('/manga/{id}/comment', [MangaController::class, 'comment'])->whereNumber('id');
    Route::post('/manga/{id}/favor', [MangaController::class, 'favor'])->whereNumber('id');
    Route::get('/manga/{id}', [MangaController::class, 'show'])->whereNumber('id');
    Route::get('/manga/{id}/{chapter}', [MangaController::class, 'read'])->whereNumber('id')->whereNumber('chapter');
});

Route::middleware([
    'web',
    AdminIpAllow::class,
    AdminOperateLog::class,
    AdminAuth::class,
    AdminPermission::class,
])->prefix('admin')->group(function () {
    Route::get('/video/manga-types', [MangaTypeAdminController::class, 'index']);
    Route::get('/video/manga-types/create', [MangaTypeAdminController::class, 'create']);
    Route::get('/video/manga-types/{id}/edit', [MangaTypeAdminController::class, 'edit'])->whereNumber('id');

    Route::get('/video/manga-tags', [MangaTagAdminController::class, 'index']);
    Route::get('/video/manga-tags/create', [MangaTagAdminController::class, 'create']);
    Route::get('/video/manga-tags/{id}/edit', [MangaTagAdminController::class, 'edit'])->whereNumber('id');
    Route::get('/video/manga-tags/list', [MangaTagAdminController::class, 'list']);
    Route::post('/video/manga-tags/save', [MangaTagAdminController::class, 'save']);
    Route::post('/video/manga-tags/delete', [MangaTagAdminController::class, 'delete']);
    Route::post('/video/manga-tags/batch', [MangaTagAdminController::class, 'batch']);
});

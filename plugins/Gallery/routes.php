<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminIpAllow;
use App\Http\Middleware\AdminOperateLog;
use App\Http\Middleware\AdminPermission;
use Illuminate\Support\Facades\Route;
use Plugins\Gallery\Http\Controllers\GalleryAuthorAdminController;
use Plugins\Gallery\Http\Controllers\GalleryController;
use Plugins\Gallery\Http\Controllers\GalleryPicAdminController;
use Plugins\Gallery\Http\Controllers\GalleryTagAdminController;
use Plugins\Gallery\Http\Controllers\GalleryWorkAdminController;

Route::middleware('web')->group(function () {
    Route::get('/gallery', [GalleryController::class, 'index']);
    Route::get('/gallery/shelf', [GalleryController::class, 'shelf']);
    Route::post('/gallery/{id}/comment', [GalleryController::class, 'comment'])->whereNumber('id');
    Route::post('/gallery/{id}/favor', [GalleryController::class, 'favor'])->whereNumber('id');
    Route::get('/gallery/{id}', [GalleryController::class, 'show'])->whereNumber('id');
});

Route::middleware([
    'web',
    AdminIpAllow::class,
    AdminOperateLog::class,
    AdminAuth::class,
    AdminPermission::class,
])->prefix('admin')->group(function () {
    Route::get('/video/galleries/create', [GalleryWorkAdminController::class, 'create']);
    Route::get('/video/galleries/{id}/edit', [GalleryWorkAdminController::class, 'edit'])->whereNumber('id');
    Route::get('/video/gallery-pics/create', [GalleryPicAdminController::class, 'create']);
    Route::get('/video/gallery-pics/{id}/edit', [GalleryPicAdminController::class, 'edit'])->whereNumber('id');

    Route::get('/video/gallery-tags', [GalleryTagAdminController::class, 'index']);
    Route::get('/video/gallery-tags/create', [GalleryTagAdminController::class, 'create']);
    Route::get('/video/gallery-tags/{id}/edit', [GalleryTagAdminController::class, 'edit'])->whereNumber('id');
    Route::get('/video/gallery-tags/list', [GalleryTagAdminController::class, 'list']);
    Route::post('/video/gallery-tags/save', [GalleryTagAdminController::class, 'save']);
    Route::post('/video/gallery-tags/delete', [GalleryTagAdminController::class, 'delete']);
    Route::post('/video/gallery-tags/batch', [GalleryTagAdminController::class, 'batch']);

    Route::get('/video/gallery-authors', [GalleryAuthorAdminController::class, 'index']);
    Route::get('/video/gallery-authors/create', [GalleryAuthorAdminController::class, 'create']);
    Route::get('/video/gallery-authors/{id}/edit', [GalleryAuthorAdminController::class, 'edit'])->whereNumber('id');
    Route::get('/video/gallery-authors/list', [GalleryAuthorAdminController::class, 'list']);
    Route::post('/video/gallery-authors/save', [GalleryAuthorAdminController::class, 'save']);
    Route::post('/video/gallery-authors/delete', [GalleryAuthorAdminController::class, 'delete']);
    Route::post('/video/gallery-authors/batch', [GalleryAuthorAdminController::class, 'batch']);
});

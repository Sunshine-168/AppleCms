<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\GbookController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\MangaController;
use App\Http\Controllers\Api\ProvideController;
use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\ReceiveController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\TimmingController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VodController;
use App\Http\Controllers\Api\WechatController;
use App\Http\Controllers\Api\ArtController;
use App\Http\Controllers\Api\ActorController;
use App\Http\Controllers\Api\WebsiteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [IndexController::class, 'index'])->name('api.index');
Route::get('/publicapi', [PublicApiController::class, 'index'])->name('api.publicapi.index');

// Video API
Route::prefix('vod')->group(function () {
    Route::get('/list', [VodController::class, 'getList'])->name('api.vod.list');
    Route::get('/detail', [VodController::class, 'getDetail'])->name('api.vod.detail');
});

// Article API
Route::prefix('art')->group(function () {
    Route::get('/list', [ArtController::class, 'getList'])->name('api.art.list');
    Route::get('/detail', [ArtController::class, 'getDetail'])->name('api.art.detail');
});

// Actor API
Route::prefix('actor')->group(function () {
    Route::get('/list', [ActorController::class, 'getList'])->name('api.actor.list');
    Route::get('/detail', [ActorController::class, 'getDetail'])->name('api.actor.detail');
});

Route::prefix('comment')->group(function () {
    Route::get('/list', [CommentController::class, 'getList'])->name('api.comment.list');
});

Route::prefix('gbook')->group(function () {
    Route::get('/list', [GbookController::class, 'getList'])->name('api.gbook.list');
});

Route::prefix('link')->group(function () {
    Route::get('/list', [LinkController::class, 'getList'])->name('api.link.list');
});

Route::prefix('manga')->group(function () {
    Route::get('/list', [MangaController::class, 'getList'])->name('api.manga.list');
    Route::get('/detail', [MangaController::class, 'getDetail'])->name('api.manga.detail');
});

Route::prefix('topic')->group(function () {
    Route::get('/list', [TopicController::class, 'getList'])->name('api.topic.list');
    Route::get('/detail', [TopicController::class, 'getDetail'])->name('api.topic.detail');
});

Route::prefix('type')->group(function () {
    Route::get('/list', [TypeController::class, 'getList'])->name('api.type.list');
    Route::get('/all', [TypeController::class, 'getAllList'])->name('api.type.all');
});

Route::prefix('user')->group(function () {
    Route::get('/list', [UserController::class, 'getList'])->name('api.user.list');
    Route::get('/detail', [UserController::class, 'getDetail'])->name('api.user.detail');
});

Route::prefix('website')->group(function () {
    Route::get('/list', [WebsiteController::class, 'getList'])->name('api.website.list');
    Route::get('/detail', [WebsiteController::class, 'getDetail'])->name('api.website.detail');
});

Route::prefix('provide')->group(function () {
    Route::get('/vod', [ProvideController::class, 'vod'])->name('api.provide.vod');
    Route::get('/art', [ProvideController::class, 'art'])->name('api.provide.art');
    Route::get('/actor', [ProvideController::class, 'actor'])->name('api.provide.actor');
    Route::get('/role', [ProvideController::class, 'role'])->name('api.provide.role');
    Route::get('/manga', [ProvideController::class, 'manga'])->name('api.provide.manga');
    Route::get('/website', [ProvideController::class, 'website'])->name('api.provide.website');
});

Route::prefix('receive')->group(function () {
    Route::match(['get', 'post'], '/vod', [ReceiveController::class, 'vod'])->name('api.receive.vod');
    Route::match(['get', 'post'], '/art', [ReceiveController::class, 'art'])->name('api.receive.art');
    Route::match(['get', 'post'], '/actor', [ReceiveController::class, 'actor'])->name('api.receive.actor');
    Route::match(['get', 'post'], '/role', [ReceiveController::class, 'role'])->name('api.receive.role');
    Route::match(['get', 'post'], '/website', [ReceiveController::class, 'website'])->name('api.receive.website');
    Route::match(['get', 'post'], '/comment', [ReceiveController::class, 'comment'])->name('api.receive.comment');
});

Route::get('/timming', [TimmingController::class, 'index'])->name('api.timming.index');
Route::match(['get', 'post'], '/wechat', [WechatController::class, 'index'])->name('api.wechat.index');

<?php

use App\Http\Controllers\Web\InstallController;
use Illuminate\Support\Facades\Route;

Route::get('/install', [InstallController::class, 'index'])->name('install');
Route::post('/install/probe', [InstallController::class, 'probe']);
Route::post('/install/task', [InstallController::class, 'task']);
Route::get('/install/done', [InstallController::class, 'done'])->name('install.done');

require __DIR__.'/admin.php';

use App\Http\Controllers\Web\InteractionController;
use App\Http\Controllers\Web\MemberController;
use App\Http\Controllers\Web\VodController;
use App\Http\Controllers\Web\SeoController;
use App\Http\Controllers\Web\PlayerController;
use App\Http\Controllers\Api\ProvideController;

Route::get('/', [VodController::class, 'index'])->name('vod.home');
Route::get('/show', [VodController::class, 'show'])->name('vod.show');
Route::get('/search', [VodController::class, 'search'])->name('vod.search');
Route::get('/type/{id}', [VodController::class, 'type'])->name('vod.type');
Route::get('/vod/{id}', [VodController::class, 'detail'])->name('vod.detail')->whereNumber('id');
Route::post('/vod/{id}/comment', [InteractionController::class, 'comment'])->middleware('throttle:10,1')->whereNumber('id');
Route::post('/vod/{id}/report', [InteractionController::class, 'report'])->middleware('throttle:8,1')->whereNumber('id');
Route::post('/vod/{id}/score', [InteractionController::class, 'score'])->middleware('throttle:20,1')->whereNumber('id');
Route::post('/vod/{id}/favorite', [InteractionController::class, 'favorite'])->middleware('throttle:20,1')->whereNumber('id');
Route::get('/play/{id}/{sid?}/{nid?}', [VodController::class, 'play'])->name('vod.play')->whereNumber('id');
Route::get('/down/{id}/{sid?}/{nid?}', [VodController::class, 'down'])->name('vod.down')->whereNumber('id');
Route::get('/player/{id}/{sid?}/{nid?}', [PlayerController::class, 'show'])->name('vod.player')->whereNumber('id');
Route::get('/tag/{slug}', [VodController::class, 'tag'])->name('vod.tag');
Route::get('/actor/{id}', [VodController::class, 'actor'])->name('vod.actor')->whereNumber('id');
Route::get('/topic/{id}', [VodController::class, 'topic'])->name('vod.topic');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('vod.sitemap');
Route::get('/rss.xml', [SeoController::class, 'rss'])->name('vod.rss');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('vod.robots');
Route::get('/api.php/provide/vod', [ProvideController::class, 'vod'])->name('vod.provide');
Route::get('/api/provide/vod', [ProvideController::class, 'vod']);

Route::prefix('index.php/vod')->group(function () {
    Route::get('type/id/{id}', [VodController::class, 'type'])->where('id', '[^/]+');
    Route::get('detail/id/{id}', [VodController::class, 'detail'])->where('id', '[0-9]+(?:\.html)?');
    Route::get('play/id/{id}/sid/{sid}/nid/{nid}', [VodController::class, 'play'])->where([
        'id' => '[0-9]+',
        'sid' => '[0-9]+',
        'nid' => '[0-9]+(?:\.html)?',
    ]);
    Route::get('down/id/{id}/sid/{sid}/nid/{nid}', [VodController::class, 'down'])->where([
        'id' => '[0-9]+',
        'sid' => '[0-9]+',
        'nid' => '[0-9]+(?:\.html)?',
    ]);
    Route::get('search{suffix?}', [VodController::class, 'search'])->where('suffix', '\.html');
    Route::get('show{suffix?}', [VodController::class, 'show'])->where('suffix', '\.html');
    Route::get('tag/id/{slug}', [VodController::class, 'tag']);
    Route::get('actor/id/{id}', [VodController::class, 'actor'])->where('id', '[0-9]+(?:\.html)?');
    Route::get('topic/id/{id}', [VodController::class, 'topic']);
});

Route::get('/member/login', [MemberController::class, 'showLogin']);
Route::post('/member/login', [MemberController::class, 'login'])->middleware('throttle:8,1');
Route::get('/member/register', [MemberController::class, 'showRegister']);
Route::post('/member/register', [MemberController::class, 'register'])->middleware('throttle:5,1');
Route::post('/member/logout', [MemberController::class, 'logout']);
Route::middleware('member.auth')->group(function () {
    Route::get('/member', [MemberController::class, 'center']);
    Route::post('/member/password', [MemberController::class, 'password']);
    Route::post('/member/redeem', [MemberController::class, 'redeem']);
    Route::get('/member/favorites', [MemberController::class, 'favorites']);
    Route::get('/member/history', [MemberController::class, 'histories']);
});

<?php

use App\Http\Controllers\Api\App\InteractionController as AppInteractionController;
use App\Http\Controllers\Api\App\MemberController as AppMemberController;
use App\Http\Controllers\Api\App\VodController as AppVodController;
use App\Http\Controllers\Api\AppController;
use App\Http\Controllers\Api\ProvideController;
use App\Http\Controllers\Api\ReceiveController;
use App\Http\Middleware\AppApiKey;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware([ThrottleRequests::class])->group(function () {
    Route::get('/provide/vod', [ProvideController::class, 'vod'])->name('vod.provide');
    Route::get('/provide/manga', [ProvideController::class, 'manga'])->name('manga.provide');
    Route::get('/app/vod', [AppController::class, 'vod'])->name('vod.app');
    Route::post('/receive/vod', [ReceiveController::class, 'vod'])->name('vod.receive');
    Route::post('/receive/manga', [ReceiveController::class, 'manga'])->name('manga.receive');
});

Route::prefix('app')->middleware([AppApiKey::class])->group(function () {
    Route::get('config', [AppVodController::class, 'config']);
    Route::get('home', [AppVodController::class, 'home']);
    Route::get('types', [AppVodController::class, 'types']);
    Route::get('type/{id}', [AppVodController::class, 'type']);
    Route::get('show', [AppVodController::class, 'show']);
    Route::get('search', [AppVodController::class, 'search']);
    Route::get('latest', [AppVodController::class, 'latest']);
    Route::get('tag/{slug}', [AppVodController::class, 'tag'])->where('slug', '[^/]+');
    Route::get('actors', [AppVodController::class, 'actors']);
    Route::get('actor/{id}', [AppVodController::class, 'actor'])->whereNumber('id');
    Route::get('topics', [AppVodController::class, 'topics']);
    Route::get('topics/search', [AppVodController::class, 'topicSearch']);
    Route::get('topic/{id}', [AppVodController::class, 'topic']);
    Route::get('arts', [AppVodController::class, 'arts']);
    Route::get('arts/type/{id}', [AppVodController::class, 'arts'])->whereNumber('id');
    Route::get('arts/tag/{slug}', [AppVodController::class, 'artTag'])->where('slug', '[^/]+');
    Route::get('art/{id}', [AppVodController::class, 'art'])->whereNumber('id');
    Route::get('roles', [AppVodController::class, 'roles']);
    Route::get('role/{id}', [AppVodController::class, 'role'])->whereNumber('id');
    Route::get('plots', [AppVodController::class, 'plots']);
    Route::get('plot/{id}', [AppVodController::class, 'plot'])->whereNumber('id');
    Route::get('websites', [AppVodController::class, 'websites']);
    Route::get('website/{id}', [AppVodController::class, 'website'])->whereNumber('id');
    Route::get('website/{id}/go', [AppVodController::class, 'websiteGo'])->whereNumber('id');

    Route::middleware('member.api:optional')->group(function () {
        Route::get('videos/{id}', [AppVodController::class, 'detail'])->whereNumber('id');
        Route::get('play/{id}/{sid?}/{nid?}', [AppVodController::class, 'play'])->whereNumber('id');
        Route::get('down/{id}/{sid?}/{nid?}', [AppVodController::class, 'down'])->whereNumber('id');
        Route::post('videos/{id}/comment', [AppInteractionController::class, 'comment'])->middleware('throttle:10,1')->whereNumber('id');
        Route::post('videos/{id}/report', [AppInteractionController::class, 'report'])->middleware('throttle:8,1')->whereNumber('id');
        Route::post('videos/{id}/score', [AppInteractionController::class, 'score'])->middleware('throttle:20,1')->whereNumber('id');
        Route::post('art/{id}/comment', [AppInteractionController::class, 'artComment'])->middleware('throttle:10,1')->whereNumber('id');
        Route::post('gbook', [AppInteractionController::class, 'guestbook'])->middleware('throttle:6,1');
        Route::post('play/fail', [AppInteractionController::class, 'playFail'])->middleware('throttle:20,1');
        Route::post('comment/{id}/report', [AppInteractionController::class, 'reportComment'])->middleware('throttle:20,1')->whereNumber('id');
        Route::post('comment/{id}/like', [AppInteractionController::class, 'likeComment'])->middleware('throttle:30,1')->whereNumber('id');
    });

    Route::post('videos/{id}/favorite', [AppInteractionController::class, 'favorite'])->middleware(['member.api', 'throttle:20,1'])->whereNumber('id');
    Route::post('videos/{id}/share', [AppInteractionController::class, 'share'])->middleware(['member.api', 'throttle:20,1'])->whereNumber('id');

    Route::post('member/login', [AppMemberController::class, 'login'])->middleware('throttle:8,1');
    Route::post('member/register', [AppMemberController::class, 'register'])->middleware('throttle:5,1');

    Route::middleware('member.api')->group(function () {
        Route::post('member/logout', [AppMemberController::class, 'logout']);
        Route::get('member', [AppMemberController::class, 'center']);
        Route::post('member/password', [AppMemberController::class, 'password']);
        Route::post('member/redeem', [AppMemberController::class, 'redeem']);
        Route::post('member/invite', [AppMemberController::class, 'generateInvite']);
        Route::get('member/invite/rank', [AppMemberController::class, 'inviteRank']);
        Route::get('member/invite/poster', [AppMemberController::class, 'invitePoster']);
        Route::get('member/favorites', [AppMemberController::class, 'favorites']);
        Route::get('member/history', [AppMemberController::class, 'histories']);
        Route::get('member/inbox', [AppMemberController::class, 'inbox']);
        Route::get('member/activity', [AppMemberController::class, 'activity']);
        Route::post('member/sign', [AppMemberController::class, 'sign'])->middleware('throttle:10,1');
    });
});

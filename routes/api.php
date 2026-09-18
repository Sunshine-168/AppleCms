<?php

use App\Http\Controllers\Api\AppController;
use App\Http\Controllers\Api\ProvideController;
use App\Http\Controllers\Api\ReceiveController;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware([ThrottleRequests::class])->group(function () {
    Route::get('/provide/vod', [ProvideController::class, 'vod'])->name('vod.provide');
    Route::get('/provide/manga', [ProvideController::class, 'manga'])->name('manga.provide');
    Route::get('/app/vod', [AppController::class, 'vod'])->name('vod.app');
    Route::post('/receive/vod', [ReceiveController::class, 'vod'])->name('vod.receive');
    Route::post('/receive/manga', [ReceiveController::class, 'manga'])->name('manga.receive');
});

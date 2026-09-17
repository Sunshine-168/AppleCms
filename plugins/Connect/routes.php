<?php

use Illuminate\Support\Facades\Route;
use Plugins\Connect\Http\Controllers\ConnectController;

Route::middleware('web')->group(function () {
    Route::get('/connect/qq', [ConnectController::class, 'redirectQq'])->middleware('throttle:20,1');
    Route::get('/connect/qq/callback', [ConnectController::class, 'callbackQq']);
    Route::get('/connect/wechat', [ConnectController::class, 'redirectWechat'])->middleware('throttle:20,1');
    Route::get('/connect/wechat/callback', [ConnectController::class, 'callbackWechat']);
});

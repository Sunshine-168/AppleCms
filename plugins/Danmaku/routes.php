<?php

use Illuminate\Support\Facades\Route;
use Plugins\Danmaku\Http\Controllers\DanmakuController;

Route::middleware('web')->group(function () {
    Route::post('/danmaku/report', [DanmakuController::class, 'report'])->middleware('throttle:20,1');
    Route::get('/danmaku/{id}', [DanmakuController::class, 'index'])->whereNumber('id');
    Route::post('/danmaku/{id}', [DanmakuController::class, 'store'])->middleware('throttle:20,1')->whereNumber('id');
});

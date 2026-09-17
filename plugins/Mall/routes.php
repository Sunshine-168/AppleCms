<?php

use Illuminate\Support\Facades\Route;
use Plugins\Mall\Http\Controllers\MallController;

Route::middleware('web')->group(function () {
    Route::get('/mall', [MallController::class, 'index']);
    Route::get('/mall/{id}', [MallController::class, 'show'])->whereNumber('id');
    Route::post('/mall/{id}/buy', [MallController::class, 'buy'])->middleware(['member.auth', 'throttle:10,1'])->whereNumber('id');
});

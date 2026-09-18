<?php

use Illuminate\Support\Facades\Route;
use Plugins\Chatroom\Http\Controllers\ChatroomController;

Route::middleware('web')->group(function () {
    Route::post('/chatroom/report', [ChatroomController::class, 'report'])->middleware('throttle:20,1');
    Route::get('/chatroom/{id}', [ChatroomController::class, 'index'])->whereNumber('id');
    Route::post('/chatroom/{id}', [ChatroomController::class, 'store'])->middleware('throttle:20,1')->whereNumber('id');
});

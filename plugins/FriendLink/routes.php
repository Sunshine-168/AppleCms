<?php

use Illuminate\Support\Facades\Route;
use Plugins\FriendLink\Http\Controllers\FriendLinkController;

Route::middleware('web')->group(function () {
    Route::get('/links/captcha', [FriendLinkController::class, 'captcha']);
    Route::get('/links/apply', [FriendLinkController::class, 'applyForm']);
    Route::post('/links/apply', [FriendLinkController::class, 'applyStore'])->middleware('throttle:8,1');
    Route::get('/links/edit/{token}', [FriendLinkController::class, 'editForm'])->where('token', '[a-f0-9]{32}');
    Route::post('/links/edit/{token}', [FriendLinkController::class, 'editStore'])->middleware('throttle:12,1')->where('token', '[a-f0-9]{32}');
    Route::get('/links/go/{id}', [FriendLinkController::class, 'go'])->whereNumber('id');
    Route::post('/links/hit', [FriendLinkController::class, 'hit'])->middleware('throttle:30,1');
});

<?php

use Illuminate\Support\Facades\Route;
use Plugins\Pay\Http\Controllers\PayController;

Route::middleware('web')->group(function () {
    Route::get('/member/pay', [PayController::class, 'index'])->middleware('member.auth');
    Route::post('/member/pay', [PayController::class, 'create'])->middleware(['member.auth', 'throttle:10,1']);
    Route::get('/member/pay/{id}', [PayController::class, 'show'])->middleware('member.auth')->whereNumber('id');
    Route::post('/pay/notify/wechat', [PayController::class, 'notifyWechat']);
    Route::post('/pay/notify/alipay', [PayController::class, 'notifyAlipay']);
    Route::get('/pay/return/alipay', [PayController::class, 'returnAlipay']);
});

<?php

use Illuminate\Support\Facades\Route;
use Plugins\Coupon\Http\Controllers\CouponController;

Route::middleware('web')->group(function () {
    Route::get('/member/coupons', [CouponController::class, 'index'])->middleware('member.auth');
    Route::post('/member/coupons/{id}/receive', [CouponController::class, 'receive'])->middleware(['member.auth', 'throttle:20,1'])->whereNumber('id');
});

<?php

use Illuminate\Support\Facades\Route;
use Plugins\Weixin\Http\Controllers\WeixinController;

Route::middleware('web')->group(function () {
    Route::any('/weixin', [WeixinController::class, 'index']);
});

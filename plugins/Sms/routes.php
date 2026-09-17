<?php

use Illuminate\Support\Facades\Route;
use Plugins\Sms\Http\Controllers\SmsController;

Route::middleware('web')->group(function () {
    Route::post('/sms/send', [SmsController::class, 'send'])->middleware('throttle:8,1');
});

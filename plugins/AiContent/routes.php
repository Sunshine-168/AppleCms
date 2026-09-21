<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminIpAllow;
use Illuminate\Support\Facades\Route;
use Plugins\AiContent\Http\Controllers\AiContentController;

Route::middleware(['web', AdminIpAllow::class, AdminAuth::class])->group(function () {
    Route::post('/admin/video/ai/generate', [AiContentController::class, 'generate'])->middleware('throttle:20,1');
    Route::post('/admin/video/ai/seo', [AiContentController::class, 'seo'])->middleware('throttle:20,1');
    Route::post('/admin/video/ai/seo/batch', [AiContentController::class, 'seoBatch'])->middleware('throttle:5,1');
});

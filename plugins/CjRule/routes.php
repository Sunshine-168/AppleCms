<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminIpAllow;
use Illuminate\Support\Facades\Route;
use Plugins\CjRule\Http\Controllers\CjRuleController;

Route::middleware(['web', AdminIpAllow::class, AdminAuth::class])->group(function () {
    Route::post('/admin/video/cj/try', [CjRuleController::class, 'tryRun']);
    Route::post('/admin/video/cj/run', [CjRuleController::class, 'import']);
});

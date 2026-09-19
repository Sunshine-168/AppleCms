<?php

use Illuminate\Support\Facades\Route;
use Plugins\Live\Http\Controllers\LiveController;

Route::middleware('web')->group(function () {
    Route::get('/live', [LiveController::class, 'index']);
    Route::get('/live/{id}', [LiveController::class, 'show'])->whereNumber('id');
});

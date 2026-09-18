<?php

use Illuminate\Support\Facades\Route;
use Plugins\PublishPage\Http\Controllers\PublishPageController;

Route::middleware('web')->group(function () {
    Route::get('/sitehome', [PublishPageController::class, 'enter']);
    Route::get('/publish/{id}', [PublishPageController::class, 'group'])->where('id', '[A-Za-z0-9_-]+');
});

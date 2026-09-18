<?php

use Illuminate\Support\Facades\Route;
use Plugins\Advert\Http\Controllers\AdvertController;

Route::middleware('web')->group(function () {
    Route::get('/ads/go/{id}', [AdvertController::class, 'go'])->whereNumber('id');
});

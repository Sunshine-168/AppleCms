<?php

use Illuminate\Support\Facades\Route;
use Plugins\Manga\Http\Controllers\MangaController;

Route::middleware('web')->group(function () {
    Route::get('/manga', [MangaController::class, 'index']);
    Route::get('/manga/{id}', [MangaController::class, 'show'])->whereNumber('id');
    Route::get('/manga/{id}/{chapter}', [MangaController::class, 'read'])->whereNumber('id')->whereNumber('chapter');
});

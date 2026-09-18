<?php

use Illuminate\Support\Facades\Route;
use Plugins\Manga\Http\Controllers\MangaController;

Route::middleware('web')->group(function () {
    Route::get('/manga', [MangaController::class, 'index']);
    Route::get('/manga/rank', [MangaController::class, 'rank']);
    Route::get('/manga/update', [MangaController::class, 'updates']);
    Route::get('/manga/shelf', [MangaController::class, 'shelf']);
    Route::get('/manga/history', [MangaController::class, 'history']);
    Route::post('/manga/{id}/comment', [MangaController::class, 'comment'])->whereNumber('id');
    Route::post('/manga/{id}/favor', [MangaController::class, 'favor'])->whereNumber('id');
    Route::get('/manga/{id}', [MangaController::class, 'show'])->whereNumber('id');
    Route::get('/manga/{id}/{chapter}', [MangaController::class, 'read'])->whereNumber('id')->whereNumber('chapter');
});

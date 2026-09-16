<?php

use Illuminate\Support\Facades\Route;
use Plugins\CodeEditor\Http\Controllers\AssetController;

Route::middleware('web')->get('/plugin-assets/code-editor/{file}', [AssetController::class, 'show'])
    ->where('file', '[A-Za-z0-9._\\/-]+')
    ->name('plugin.code-editor.asset');

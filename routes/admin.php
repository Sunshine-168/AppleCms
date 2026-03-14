<?php
use Illuminate\Support\Facades\Route;

Route::get('/admin', fn () => view('admin.index'));
Route::get('/admin/login', fn () => view('admin.login'));


Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::get('/login', [LoginController::class, 'showLogin']);
});

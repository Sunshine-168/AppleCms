<?php
use Illuminate\Support\Facades\Route;

Route::get('/admin', fn () => redirect('/views/index.html'));
Route::get('/admin/login', fn () => redirect('/views/user/login.html'));

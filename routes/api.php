<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\System\SysUser;

Route::post('admin/login', [SysUser::class, 'login']);

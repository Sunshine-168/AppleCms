<?php



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\Sys\SysUser;

Route::post('admin/login', [SysUser::class, 'login']);

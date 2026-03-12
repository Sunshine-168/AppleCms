<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'admin/index/unlocked',
        'admin/admin/index/unlocked',
        'index.php/admin/index/unlocked',
        'index.php/admin/admin/index/unlocked',
        'admin/upload/upload',
        'index.php/admin/upload/upload',
    ];
}

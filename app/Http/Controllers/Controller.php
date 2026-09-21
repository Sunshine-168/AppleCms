<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function bindQueryIfMissing(\Illuminate\Http\Request $request, string $key, mixed $value): void
    {
        $n = (int) $value;
        if ($n > 0 && ! $request->query->has($key)) {
            $request->query->set($key, (string) $n);
            $request->merge([$key => $n]);
        }
    }
}

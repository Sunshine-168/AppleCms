<?php

namespace Plugins\CodeEditor\Http\Controllers;

use Illuminate\Http\Response;

class AssetController
{
    public function show(string $file): Response
    {
        $file = str_replace('\\', '/', $file);
        if ($file === '' || str_contains($file, '..')) {
            abort(404);
        }
        $root = realpath(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'assets');
        if ($root === false) {
            abort(404);
        }
        $path = realpath($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $file));
        if ($path === false || ! is_file($path) || ! str_starts_with($path, $root)) {
            abort(404);
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext !== 'js' && $ext !== 'css') {
            abort(404);
        }
        $mime = $ext === 'js'
            ? 'application/javascript; charset=UTF-8'
            : 'text/css; charset=UTF-8';

        $raw = file_get_contents($path);
        if ($raw === false) {
            abort(404);
        }

        return response($raw, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=120',
        ]);
    }
}

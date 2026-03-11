<?php

namespace App\Http\Controllers\Web\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class VerifyController extends Controller
{
    public function page(Request $request, string $type = 'show')
    {
        $type = in_array($type, ['search', 'show'], true) ? $type : 'show';

        return response()
            ->view('public.verify', [
                'type' => $type,
                'id' => (string) $request->input('id', ''),
                'rootPath' => rtrim(url('/'), '/'),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function index(string $id = '')
    {
        $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4));
        Session::put($this->sessionKey($id), $code);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="120" height="40" viewBox="0 0 120 40">
  <rect width="120" height="40" fill="#f8f9fa"/>
  <line x1="10" y1="10" x2="110" y2="30" stroke="#ced4da" stroke-width="1"/>
  <line x1="15" y1="30" x2="100" y2="8" stroke="#dee2e6" stroke-width="1"/>
  <text x="12" y="28" font-size="24" font-family="monospace" letter-spacing="6" fill="#212529">{$code}</text>
</svg>
SVG;

        return response($svg, 200)->header('Content-Type', 'image/svg+xml; charset=utf-8');
    }

    public function check(Request $request, string $verify = '', string $id = '')
    {
        $input = $verify !== '' ? $verify : (string) $request->input('verify', '');
        $type = (string) $request->input('type', '');
        $saved = (string) Session::get($this->sessionKey($id), '');

        if ($input === '') {
            return response()->json([
                'code' => 1001,
                'msg' => __('verify_empty'),
            ]);
        }

        $passed = strcasecmp($saved, $input) === 0;
        if ($passed && in_array($type, ['search', 'show'], true)) {
            Session::put($type . '_verify', '1');
        }

        return response()->json([
            'code' => $passed ? 1 : 1002,
            'msg' => $passed ? 'ok' : __('verify_err'),
        ]);
    }

    protected function sessionKey(string $id = ''): string
    {
        return 'verify_code_' . ($id === '' ? 'default' : $id);
    }
}

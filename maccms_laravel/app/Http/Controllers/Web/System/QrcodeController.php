<?php

namespace App\Http\Controllers\Web\System;

use App\Http\Controllers\Controller;
use App\Utils\QRcode;
use Illuminate\Http\Request;

class QrcodeController extends Controller
{
    public function index(Request $request)
    {
        $url = $request->input('url', '');
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Invalid url');
        }

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        ob_start();
        QRcode::png($url, false, QR_ECLEVEL_M, 10, 2);
        $content = ob_get_clean();

        return response($content, 200)->header('Content-Type', 'image/png');
    }
}

<?php

namespace Plugins\Weixin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Plugins\Weixin\Services\WeixinService;

class WeixinController extends Controller
{
    public function __construct(private readonly WeixinService $weixin) {}

    public function index(Request $request): Response
    {
        $token = $this->weixin->token();
        if ($token === '') {
            return response('未配置 Token', 403);
        }
        $signature = (string) $request->query('signature', '');
        $timestamp = (string) $request->query('timestamp', '');
        $nonce = (string) $request->query('nonce', '');
        if (! $this->weixin->checkSignature($signature, $timestamp, $nonce)) {
            return response('签名错误', 403);
        }
        if ($request->isMethod('get')) {
            return response((string) $request->query('echostr', ''), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }
        $xml = $this->weixin->reply($request->getContent());

        return response($xml, 200, ['Content-Type' => 'text/xml; charset=UTF-8']);
    }
}

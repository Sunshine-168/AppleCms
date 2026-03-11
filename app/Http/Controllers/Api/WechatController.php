<?php

namespace App\Http\Controllers\Api;

use App\Utils\WechatPublic;
use Illuminate\Http\Request;

class WechatController extends BaseController
{
    public function index(Request $request)
    {
        $config = config('maccms.weixin', []);
        if (($config['status'] ?? 0) == 0) {
            return response('closed', 403);
        }

        if ($request->has('echostr')) {
            return $this->validateWechat($request, $config);
        }

        $wechat = new WechatPublic($config);
        ob_start();
        $wechat->responseMsg();
        $content = ob_get_clean();

        return response($content, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    protected function validateWechat(Request $request, array $config)
    {
        $signature = (string) $request->query('signature', '');
        $timestamp = (string) $request->query('timestamp', '');
        $nonce = (string) $request->query('nonce', '');
        $echostr = (string) $request->query('echostr', '');
        $token = (string) ($config['token'] ?? '');

        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr);
        $tmpStr = sha1(implode($tmpArr));

        if ($tmpStr !== $signature) {
            return response('invalid signature', 403);
        }

        return response($echostr, 200)->header('Content-Type', 'text/plain; charset=utf-8');
    }
}

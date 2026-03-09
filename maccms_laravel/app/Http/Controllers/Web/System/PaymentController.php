<?php

namespace App\Http\Controllers\Web\System;

use App\Http\Controllers\Web\BaseController;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    public function notify(Request $request)
    {
        $payType = (string) $request->input('pay_type', '');
        if ($payType === '') {
            return response('支付方式不能为空', 400);
        }

        $payConfig = config('maccms.pay.' . $payType, []);
        if (empty($payConfig['appid'])) {
            return response('支付接口未开启', 400);
        }

        $class = 'App\\Libraries\\Pay\\' . ucfirst($payType);
        if (!class_exists($class)) {
            return response('支付接口不存在', 404);
        }

        try {
            $handler = app($class);
            if (!method_exists($handler, 'notify')) {
                return response('支付回调方法不存在', 500);
            }

            ob_start();
            $handler->notify();
            $content = trim((string) ob_get_clean());

            return response($content === '' ? 'success' : $content);
        } catch (\Throwable $e) {
            return response('fail:' . $e->getMessage(), 500);
        }
    }
}

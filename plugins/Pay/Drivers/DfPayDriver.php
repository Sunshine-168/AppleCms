<?php

namespace Plugins\Pay\Drivers;

use App\Models\Member\MemberOrder;
use App\Services\Video\MemberOrderService;
use Illuminate\Support\Facades\Http;
use Plugins\Pay\Models\PayChannel;

/**
 * A13 DfPay 风格：partnerid + payType + MD5(key=) 大写。三方可按这套协议对接。
 */
class DfPayDriver implements PayDriver
{
    public function __construct(private readonly MemberOrderService $orders) {}

    public function create(MemberOrder $order, PayChannel $channel): array
    {
        $url = trim((string) $channel->api_url);
        $mch = trim((string) $channel->mch_id);
        $key = trim((string) $channel->app_key);
        $payType = trim((string) $channel->code);
        if ($url === '' || $mch === '' || $key === '' || $payType === '') {
            return ['code' => 1, 'msg' => 'DfPay 通道未配齐 api_url / 商户号 / 密钥 / 产品码'];
        }

        $param = [
            'partnerid' => $mch,
            'out_trade_no' => (string) $order->order_no,
            'amount' => number_format(((int) $order->amount) / 100, 2, '.', ''),
            'payType' => $payType,
            'notifyUrl' => url('/pay/notify/dfpay'),
            'returnUrl' => url('/pay/return/dfpay'),
            'version' => '1.0',
        ];
        $param['sign'] = $this->sign($param, $key);

        try {
            $res = Http::asForm()->timeout(15)->post($url, $param);
            $json = $res->json();
        } catch (\Throwable $e) {
            return ['code' => 1, 'msg' => $e->getMessage() !== '' ? $e->getMessage() : 'DfPay 下单失败'];
        }
        if (! is_array($json)) {
            return ['code' => 1, 'msg' => 'DfPay 返回无法解析'];
        }
        $code = (int) ($json['code'] ?? 0);
        $payUrl = trim((string) ($json['url'] ?? $json['pay_url'] ?? $json['payurl'] ?? ''));
        if ($code !== 200 || $payUrl === '') {
            return ['code' => 1, 'msg' => (string) ($json['msg'] ?? $json['message'] ?? 'DfPay 未返回收银台')];
        }

        return [
            'code' => 0,
            'msg' => 'ok',
            'data' => [
                'channel' => 'dfpay',
                'pay_url' => $payUrl,
                'mode' => 'jump',
            ],
        ];
    }

    public function notify(array $payload): string
    {
        unset($payload['TongDao'], $payload['ZhiFuTongDao'], $payload['s']);
        $orderNo = trim((string) ($payload['out_trade_no'] ?? $payload['order_no'] ?? $payload['orderno'] ?? ''));
        if ($orderNo === '') {
            return 'fail';
        }
        $order = MemberOrder::query()->where('order_no', $orderNo)->first();
        if (! $order) {
            return 'fail';
        }
        $channel = $this->resolveChannel($order);
        if (! $channel) {
            return 'fail';
        }
        $key = trim((string) $channel->app_key);
        $sign = strtoupper((string) ($payload['sign'] ?? ''));
        $check = $this->sign($payload, $key);
        if ($key === '' || $sign === '' || ! hash_equals($check, $sign)) {
            return 'fail';
        }
        $amount = (float) ($payload['amount'] ?? $payload['money'] ?? 0);
        if ($amount > 0 && (int) round($amount * 100) !== (int) $order->amount) {
            return 'fail';
        }
        $ok = $this->orders->settle(
            (int) $order->id,
            (string) ($payload['trade_no'] ?? $payload['transaction_id'] ?? ''),
            'dfpay'
        );

        return (($ok['code'] ?? 1) === 0) ? 'success' : 'fail';
    }

    /** @param  array<string, mixed>  $data */
    public function sign(array $data, string $key): string
    {
        unset($data['sign'], $data['TongDao'], $data['ZhiFuTongDao'], $data['s']);
        ksort($data);
        $parts = [];
        foreach ($data as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $parts[] = $k.'='.$v;
        }

        return strtoupper(md5(implode('&', $parts).'&key='.$key));
    }

    private function resolveChannel(MemberOrder $order): ?PayChannel
    {
        $cid = (int) ($order->pay_channel_id ?? 0);
        if ($cid > 0) {
            $row = PayChannel::query()->find($cid);
            if ($row) {
                return $row;
            }
        }

        return PayChannel::query()->where('driver', 'dfpay')->where('status', 1)->orderByDesc('sort')->orderBy('id')->first();
    }
}

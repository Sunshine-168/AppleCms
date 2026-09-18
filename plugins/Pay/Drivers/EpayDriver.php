<?php

namespace Plugins\Pay\Drivers;

use App\Models\Member\MemberOrder;
use App\Services\Video\MemberOrderService;
use Illuminate\Support\Facades\Http;
use Plugins\Pay\Models\PayChannel;

/**
 * 易支付（MacCMS Epay 同款）。多数聚合通道可直接填 api_url / pid / key。
 */
class EpayDriver implements PayDriver
{
    public function __construct(private readonly MemberOrderService $orders) {}

    public function create(MemberOrder $order, PayChannel $channel): array
    {
        $api = rtrim(trim((string) $channel->api_url), '/').'/';
        $pid = trim((string) $channel->mch_id);
        $key = trim((string) $channel->app_key);
        if ($api === '/' || $pid === '' || $key === '') {
            return ['code' => 1, 'msg' => '易支付通道未配齐 api_url / 商户号 / 密钥'];
        }

        $data = [
            'pid' => $pid,
            'type' => trim((string) $channel->code),
            'notify_url' => url('/pay/notify/epay'),
            'return_url' => url('/pay/return/epay'),
            'out_trade_no' => (string) $order->order_no,
            'name' => '积分充值 '.$order->order_no,
            'money' => number_format(((int) $order->amount) / 100, 2, '.', ''),
        ];
        if ($data['type'] === '') {
            unset($data['type']);
        }
        $sign = $this->sign($data, $key);
        $query = http_build_query(array_merge($data, ['sign' => $sign, 'sign_type' => 'MD5']));
        $payUrl = $api.'submit.php?'.$query;

        return [
            'code' => 0,
            'msg' => 'ok',
            'data' => [
                'channel' => 'epay',
                'pay_url' => $payUrl,
                'mode' => 'jump',
            ],
        ];
    }

    public function notify(array $payload): string
    {
        $orderNo = trim((string) ($payload['out_trade_no'] ?? ''));
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
        $sign = (string) ($payload['sign'] ?? '');
        if ($key === '' || $sign === '' || ! hash_equals($this->sign($payload, $key), $sign)) {
            return 'fail';
        }
        $tradeStatus = (string) ($payload['trade_status'] ?? $payload['status'] ?? '');
        // 易支付常见：TRADE_SUCCESS；部分只回 out_trade_no+sign
        if ($tradeStatus !== '' && ! in_array(strtoupper($tradeStatus), ['TRADE_SUCCESS', 'SUCCESS', '1', '2'], true)) {
            return 'success';
        }
        $money = (float) ($payload['money'] ?? $payload['amount'] ?? 0);
        if ($money > 0 && (int) round($money * 100) !== (int) $order->amount) {
            return 'fail';
        }
        $ok = $this->orders->settle((int) $order->id, (string) ($payload['trade_no'] ?? ''), 'epay');

        return (($ok['code'] ?? 1) === 0) ? 'success' : 'fail';
    }

    /** Optional mapi JSON create — used when api prefers API mode. */
    public function createMapi(MemberOrder $order, PayChannel $channel): array
    {
        $api = rtrim(trim((string) $channel->api_url), '/').'/';
        $pid = trim((string) $channel->mch_id);
        $key = trim((string) $channel->app_key);
        $data = [
            'pid' => $pid,
            'type' => trim((string) $channel->code) ?: 'alipay',
            'notify_url' => url('/pay/notify/epay'),
            'return_url' => url('/pay/return/epay'),
            'out_trade_no' => (string) $order->order_no,
            'name' => '积分充值 '.$order->order_no,
            'money' => number_format(((int) $order->amount) / 100, 2, '.', ''),
        ];
        $data['sign'] = $this->sign($data, $key);
        $data['sign_type'] = 'MD5';
        try {
            $res = Http::asForm()->timeout(15)->post($api.'mapi.php', $data);
            $json = $res->json();
        } catch (\Throwable $e) {
            return ['code' => 1, 'msg' => $e->getMessage() !== '' ? $e->getMessage() : '易支付下单失败'];
        }
        if (! is_array($json)) {
            return ['code' => 1, 'msg' => '易支付返回无法解析'];
        }
        $payUrl = (string) ($json['payurl'] ?? $json['qrcode'] ?? $json['urlscheme'] ?? '');
        if ($payUrl === '') {
            return ['code' => 1, 'msg' => (string) ($json['msg'] ?? '易支付未返回支付链接')];
        }

        return [
            'code' => 0,
            'msg' => 'ok',
            'data' => [
                'channel' => 'epay',
                'pay_url' => $payUrl,
                'mode' => isset($json['qrcode']) && ! isset($json['payurl']) ? 'qrcode' : 'jump',
                'code_url' => (string) ($json['qrcode'] ?? ''),
            ],
        ];
    }

    /** @param  array<string, mixed>  $data */
    public function sign(array $data, string $key): string
    {
        unset($data['sign'], $data['sign_type'], $data['TongDao'], $data['s']);
        ksort($data);
        $parts = [];
        foreach ($data as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $parts[] = $k.'='.$v;
        }

        return md5(implode('&', $parts).$key);
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

        return PayChannel::query()->where('driver', 'epay')->where('status', 1)->orderByDesc('sort')->orderBy('id')->first();
    }
}

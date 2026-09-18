<?php

namespace Plugins\Pay\Services;

use App\Models\Member\Member;
use App\Models\Member\MemberOrder;
use App\Services\Video\MemberOrderService;
use App\Services\Video\VideoSettingService;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PayService
{
    /** @var list<int> */
    public const PACKAGES = [10, 30, 50, 100];

    public function __construct(
        private readonly VideoSettingService $settings,
        private readonly MemberOrderService $orders,
    ) {}

    public function wechatReady(): bool
    {
        return $this->opt('pay_wechat_appid') !== ''
            && $this->opt('pay_wechat_mchid') !== ''
            && $this->opt('pay_wechat_key') !== '';
    }

    public function alipayReady(): bool
    {
        return $this->opt('pay_alipay_appid') !== '' && $this->opt('pay_alipay_key') !== '';
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function create(Member $member, string $channel, float $yuan, int $couponUserId = 0): array
    {
        $yuan = round($yuan, 2);
        if ($yuan < 0.01) {
            return Result::fail('金额无效');
        }
        $channel = $channel === 'alipay' ? 'alipay' : 'wechat';
        if ($channel === 'wechat' && ! $this->wechatReady()) {
            return Result::fail('未配置微信支付参数');
        }
        if ($channel === 'alipay' && ! $this->alipayReady()) {
            return Result::fail('未配置支付宝参数');
        }
        if (! Schema::hasTable('member_orders')) {
            return Result::fail('订单表不存在');
        }
        $fen = (int) round($yuan * 100);
        $originalFen = $fen;
        $discountFen = 0;
        $payFen = $fen;
        if ($couponUserId > 0) {
            if (! class_exists(\Plugins\Coupon\Services\CouponService::class)) {
                return Result::fail('优惠券插件未启用');
            }
            $priced = app(\Plugins\Coupon\Services\CouponService::class)->quote('recharge', $yuan, $couponUserId, (int) $member->id);
            if (($priced['code'] ?? 1) !== 0) {
                return $priced;
            }
            $originalFen = (int) ($priced['data']['original_fen'] ?? $fen);
            $discountFen = (int) ($priced['data']['discount_fen'] ?? 0);
            $payFen = (int) ($priced['data']['pay_fen'] ?? $fen);
            if ($payFen < 1) {
                return Result::fail('全额抵扣不成单，至少要付 0.01 元');
            }
        }
        $now = time();
        $payload = [
            'member_id' => (int) $member->id,
            'order_no' => 'P'.date('YmdHis').Str::upper(Str::random(6)),
            'amount' => $payFen,
            'points' => $originalFen,
            'status' => 0,
            'channel' => $channel,
            'trade_no' => '',
            'remark' => $discountFen > 0 ? '在线充值 券抵 '.$discountFen.' 分' : '在线充值',
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if (Schema::hasColumn('member_orders', 'original_amount')) {
            $payload['original_amount'] = $originalFen;
        }
        if (Schema::hasColumn('member_orders', 'coupon_user_id')) {
            $payload['coupon_user_id'] = $couponUserId;
        }
        if (Schema::hasColumn('member_orders', 'coupon_discount')) {
            $payload['coupon_discount'] = $discountFen;
        }
        $order = MemberOrder::query()->create($payload);
        if ($couponUserId > 0) {
            $hold = app(\Plugins\Coupon\Services\CouponService::class)->reserve($couponUserId, (int) $member->id, (int) $order->id, (string) $order->order_no);
            if (($hold['code'] ?? 1) !== 0) {
                $order->status = 2;
                $order->remark = mb_substr((string) ($hold['msg'] ?? '占券失败'), 0, 250);
                $order->updated_at = time();
                $order->save();

                return $hold;
            }
        }

        if ($channel === 'wechat') {
            $pay = $this->wechatNative($order);
        } else {
            $pay = $this->alipayPage($order);
        }
        if (($pay['code'] ?? 1) !== 0) {
            $order->status = 2;
            $order->remark = mb_substr((string) ($pay['msg'] ?? '下单失败'), 0, 250);
            $order->updated_at = time();
            $order->save();
            if ($couponUserId > 0 && class_exists(\Plugins\Coupon\Services\CouponService::class)) {
                app(\Plugins\Coupon\Services\CouponService::class)->release($couponUserId);
            }

            return $pay;
        }

        return Result::success(array_merge(['id' => $order->id, 'order_no' => $order->order_no], $pay['data'] ?? []), '已下单');
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function wechatNative(MemberOrder $order): array
    {
        $nonce = Str::random(16);
        $params = [
            'appid' => $this->opt('pay_wechat_appid'),
            'mch_id' => $this->opt('pay_wechat_mchid'),
            'nonce_str' => $nonce,
            'body' => mb_substr('积分充值 '.$order->order_no, 0, 120),
            'out_trade_no' => (string) $order->order_no,
            'total_fee' => (string) (int) $order->amount,
            'spbill_create_ip' => '127.0.0.1',
            'notify_url' => url('/pay/notify/wechat'),
            'trade_type' => 'NATIVE',
            'product_id' => (string) $order->id,
        ];
        $params['sign'] = $this->wechatSign($params, $this->opt('pay_wechat_key'));
        try {
            $res = Http::timeout(15)
                ->withBody($this->toXml($params), 'text/xml')
                ->post('https://api.mch.weixin.qq.com/pay/unifiedorder');
        } catch (\Throwable $e) {
            return Result::fail($e->getMessage() !== '' ? $e->getMessage() : '微信下单失败');
        }
        $xml = $this->fromXml($res->body());
        if (($xml['return_code'] ?? '') !== 'SUCCESS' || ($xml['result_code'] ?? '') !== 'SUCCESS') {
            $msg = (string) ($xml['err_code_des'] ?? $xml['return_msg'] ?? '微信下单失败');

            return Result::fail($msg !== '' ? $msg : '微信下单失败');
        }
        $codeUrl = trim((string) ($xml['code_url'] ?? ''));
        if ($codeUrl === '') {
            return Result::fail('微信未返回付款码');
        }

        return Result::success(['code_url' => $codeUrl, 'channel' => 'wechat']);
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function alipayPage(MemberOrder $order): array
    {
        $appId = $this->opt('pay_alipay_appid');
        $key = $this->opt('pay_alipay_key');
        $biz = json_encode([
            'out_trade_no' => (string) $order->order_no,
            'product_code' => 'FAST_INSTANT_TRADE_PAY',
            'total_amount' => number_format(((int) $order->amount) / 100, 2, '.', ''),
            'subject' => '积分充值 '.$order->order_no,
        ], JSON_UNESCAPED_UNICODE);
        $params = [
            'app_id' => $appId,
            'method' => 'alipay.trade.page.pay',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => url('/pay/notify/alipay'),
            'return_url' => url('/pay/return/alipay'),
            'biz_content' => (string) $biz,
        ];
        $sign = $this->alipaySign($params, $key);
        if ($sign === '') {
            return Result::fail('支付宝私钥无法解析');
        }
        $params['sign'] = $sign;

        return Result::success(['channel' => 'alipay', 'action' => 'https://openapi.alipay.com/gateway.do', 'fields' => $params]);
    }

    public function handleWechatNotify(string $xml): string
    {
        $data = $this->fromXml($xml);
        if ($data === []) {
            return $this->wechatReply(false, 'empty');
        }
        $key = $this->opt('pay_wechat_key');
        if ($key === '') {
            return $this->wechatReply(false, '未配置支付参数');
        }
        $sign = strtoupper((string) ($data['sign'] ?? ''));
        if ($sign === '' || $sign !== $this->wechatSign($data, $key)) {
            return $this->wechatReply(false, 'sign');
        }
        if (($data['return_code'] ?? '') !== 'SUCCESS' || ($data['result_code'] ?? '') !== 'SUCCESS') {
            return $this->wechatReply(false, 'result');
        }
        $orderNo = (string) ($data['out_trade_no'] ?? '');
        $order = $this->findOrder($orderNo);
        if (! $order) {
            return $this->wechatReply(false, 'order');
        }
        $paid = (int) ($data['total_fee'] ?? 0);
        if ($paid > 0 && $paid !== (int) $order->amount) {
            return $this->wechatReply(false, 'amount');
        }
        $ok = $this->orders->settle((int) $order->id, (string) ($data['transaction_id'] ?? ''), 'wechat');

        return $this->wechatReply(($ok['code'] ?? 1) === 0, (string) ($ok['msg'] ?? ''));
    }

    public function handleAlipayNotify(array $payload): string
    {
        $public = $this->opt('pay_alipay_public');
        if ($public === '') {
            return 'fail';
        }
        if (! $this->alipayVerify($payload, $public)) {
            return 'fail';
        }
        $status = (string) ($payload['trade_status'] ?? '');
        if (! in_array($status, ['TRADE_SUCCESS', 'TRADE_FINISHED'], true)) {
            return 'success';
        }
        $order = $this->findOrder((string) ($payload['out_trade_no'] ?? ''));
        if (! $order) {
            return 'fail';
        }
        $paidYuan = (float) ($payload['total_amount'] ?? 0);
        if ($paidYuan > 0 && (int) round($paidYuan * 100) !== (int) $order->amount) {
            return 'fail';
        }
        $ok = $this->orders->settle((int) $order->id, (string) ($payload['trade_no'] ?? ''), 'alipay');

        return (($ok['code'] ?? 1) === 0) ? 'success' : 'fail';
    }

    public function findOrder(string $orderNo): ?MemberOrder
    {
        $orderNo = trim($orderNo);
        if ($orderNo === '') {
            return null;
        }

        return MemberOrder::query()->where('order_no', $orderNo)->first();
    }

    /** @param array<string, mixed> $data */
    public function wechatSign(array $data, string $key): string
    {
        unset($data['sign']);
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

    /** @param array<string, mixed> $params */
    public function alipaySign(array $params, string $key): string
    {
        $pem = $this->rsaPrivatePem($key);
        $res = openssl_pkey_get_private($pem);
        if ($res === false) {
            return '';
        }
        $ok = openssl_sign($this->alipayQuery($params), $binary, $res, OPENSSL_ALGO_SHA256);

        return $ok ? base64_encode($binary) : '';
    }

    /** @param array<string, mixed> $payload */
    public function alipayVerify(array $payload, string $public): bool
    {
        $sign = (string) ($payload['sign'] ?? '');
        if ($sign === '') {
            return false;
        }
        $pem = $this->rsaPublicPem($public);
        $res = openssl_pkey_get_public($pem);
        if ($res === false) {
            return false;
        }
        unset($payload['sign'], $payload['sign_type']);

        return openssl_verify($this->alipayQuery($payload), base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1;
    }

    /** @param array<string, mixed> $params */
    private function alipayQuery(array $params): string
    {
        unset($params['sign']);
        ksort($params);
        $parts = [];
        foreach ($params as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $parts[] = $k.'='.$v;
        }

        return implode('&', $parts);
    }

    private function rsaPrivatePem(string $raw): string
    {
        $raw = trim($raw);
        if (str_contains($raw, 'BEGIN')) {
            return $raw;
        }
        $body = trim(chunk_split(preg_replace('/\s+/', '', $raw) ?: '', 64, "\n"));

        return "-----BEGIN RSA PRIVATE KEY-----\n".$body."\n-----END RSA PRIVATE KEY-----";
    }

    private function rsaPublicPem(string $raw): string
    {
        $raw = trim($raw);
        if (str_contains($raw, 'BEGIN')) {
            return $raw;
        }
        $body = trim(chunk_split(preg_replace('/\s+/', '', $raw) ?: '', 64, "\n"));

        return "-----BEGIN PUBLIC KEY-----\n".$body."\n-----END PUBLIC KEY-----";
    }

    /** @param array<string, mixed> $data */
    private function toXml(array $data): string
    {
        $xml = '<xml>';
        foreach ($data as $k => $v) {
            $xml .= '<'.$k.'><![CDATA['.$v.']]></'.$k.'>';
        }

        return $xml.'</xml>';
    }

    /** @return array<string, string> */
    private function fromXml(string $xml): array
    {
        $xml = trim($xml);
        if ($xml === '') {
            return [];
        }
        $prev = libxml_use_internal_errors(true);
        $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        libxml_use_internal_errors($prev);
        if ($obj === false) {
            return [];
        }
        $json = json_encode($obj);
        $arr = is_string($json) ? json_decode($json, true) : [];

        return is_array($arr) ? array_map(static fn ($v) => is_scalar($v) ? (string) $v : '', $arr) : [];
    }

    private function wechatReply(bool $ok, string $msg): string
    {
        $code = $ok ? 'SUCCESS' : 'FAIL';
        $msg = $msg !== '' ? $msg : ($ok ? 'OK' : 'FAIL');

        return '<xml><return_code><![CDATA['.$code.']]></return_code><return_msg><![CDATA['.$msg.']]></return_msg></xml>';
    }

    private function opt(string $key): string
    {
        return trim((string) $this->settings->get($key, ''));
    }
}

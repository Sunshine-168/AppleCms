<?php

namespace Plugins\Sms\Services;

use App\Services\Video\VideoSettingService;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Plugins\Sms\Models\SmsCode;

class SmsService
{
    public function __construct(private readonly VideoSettingService $settings) {}

    public function ready(): bool
    {
        return $this->opt('sms_key') !== ''
            && $this->opt('sms_secret') !== ''
            && $this->opt('sms_sign') !== ''
            && $this->opt('sms_tpl_code') !== '';
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function send(string $phone, string $scene = 'register'): array
    {
        $phone = $this->normalizePhone($phone);
        if ($phone === '') {
            return Result::fail('手机号无效');
        }
        if (! $this->ready()) {
            return Result::fail('未配置短信参数');
        }
        if (! Schema::hasTable('plugin_sms_codes')) {
            return Result::fail('验证码表不存在');
        }
        $lockKey = 'sms.send.'.$phone;
        if (! Cache::add($lockKey, 1, 60)) {
            return Result::fail('发送太频繁，请稍后再试');
        }
        $code = (string) random_int(100000, 999999);
        $provider = strtolower($this->opt('sms_provider') ?: 'aliyun');
        $sent = $provider === 'tencent' ? $this->sendTencent($phone, $code) : $this->sendAliyun($phone, $code);
        if (($sent['code'] ?? 1) !== 0) {
            Cache::forget($lockKey);

            return $sent;
        }
        $now = time();
        SmsCode::query()->create([
            'phone' => $phone,
            'code' => $code,
            'scene' => $scene,
            'expire_at' => $now + 300,
            'created_at' => $now,
        ]);

        return Result::success([], '验证码已发送');
    }

    public function verify(string $phone, string $code, string $scene = 'register'): array
    {
        $phone = $this->normalizePhone($phone);
        $code = trim($code);
        if ($phone === '' || $code === '') {
            return Result::fail('请填写手机号和验证码');
        }
        if (! Schema::hasTable('plugin_sms_codes')) {
            return Result::fail('验证码表不存在');
        }
        $row = SmsCode::query()
            ->where('phone', $phone)
            ->where('scene', $scene)
            ->where('expire_at', '>=', time())
            ->orderByDesc('id')
            ->first();
        if (! $row || ! hash_equals((string) $row->code, $code)) {
            return Result::fail('验证码不正确或已过期');
        }
        $row->delete();

        return Result::success(['phone' => $phone], '验证通过');
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?: '';
        if (strlen($phone) === 13 && str_starts_with($phone, '86')) {
            $phone = substr($phone, 2);
        }
        if (! preg_match('/^1[3-9]\d{9}$/', $phone)) {
            return '';
        }

        return $phone;
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    private function sendAliyun(string $phone, string $code): array
    {
        $params = [
            'AccessKeyId' => $this->opt('sms_key'),
            'Action' => 'SendSms',
            'Format' => 'JSON',
            'PhoneNumbers' => $phone,
            'RegionId' => 'cn-hangzhou',
            'SignName' => $this->opt('sms_sign'),
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => bin2hex(random_bytes(8)),
            'SignatureVersion' => '1.0',
            'TemplateCode' => $this->opt('sms_tpl_code'),
            'TemplateParam' => json_encode(['code' => $code], JSON_UNESCAPED_UNICODE),
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'Version' => '2017-05-25',
        ];
        $params['Signature'] = $this->aliyunSign($params, $this->opt('sms_secret'));
        try {
            $res = Http::timeout(15)->asForm()->post('https://dysmsapi.aliyuncs.com/', $params);
        } catch (\Throwable $e) {
            return Result::fail($e->getMessage() !== '' ? $e->getMessage() : '短信发送失败');
        }
        $json = $res->json();
        $ok = is_array($json) && strtoupper((string) ($json['Code'] ?? '')) === 'OK';
        if (! $ok) {
            $msg = is_array($json) ? (string) ($json['Message'] ?? $json['Code'] ?? '短信发送失败') : '短信发送失败';

            return Result::fail($msg !== '' ? $msg : '短信发送失败');
        }

        return Result::success();
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    private function sendTencent(string $phone, string $code): array
    {
        $appid = $this->opt('sms_key');
        $appkey = $this->opt('sms_secret');
        $random = (string) random_int(100000, 999999999);
        $time = time();
        $sig = hash('sha256', 'appkey='.$appkey.'&random='.$random.'&time='.$time.'&mobile='.$phone);
        $url = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid='.rawurlencode($appid).'&random='.$random;
        $payload = [
            'tel' => ['nationcode' => '86', 'mobile' => $phone],
            'tpl_id' => (int) $this->opt('sms_tpl_code') ?: $this->opt('sms_tpl_code'),
            'params' => [$code],
            'sig' => $sig,
            'time' => $time,
            'sign' => $this->opt('sms_sign'),
        ];
        try {
            $res = Http::timeout(15)->post($url, $payload);
        } catch (\Throwable $e) {
            return Result::fail($e->getMessage() !== '' ? $e->getMessage() : '短信发送失败');
        }
        $json = $res->json();
        if (! is_array($json) || (int) ($json['result'] ?? -1) !== 0) {
            $msg = is_array($json) ? (string) ($json['errmsg'] ?? '短信发送失败') : '短信发送失败';

            return Result::fail($msg !== '' ? $msg : '短信发送失败');
        }

        return Result::success();
    }

    /** @param array<string, string> $params */
    private function aliyunSign(array $params, string $secret): string
    {
        ksort($params);
        $parts = [];
        foreach ($params as $k => $v) {
            $parts[] = $this->aliyunEnc($k).'='.$this->aliyunEnc((string) $v);
        }
        $stringToSign = 'POST&'.$this->aliyunEnc('/').'&'.$this->aliyunEnc(implode('&', $parts));

        return base64_encode(hash_hmac('sha1', $stringToSign, $secret.'&', true));
    }

    private function aliyunEnc(string $value): string
    {
        return str_replace(['+', '*', '%7E'], ['%20', '%2A', '~'], rawurlencode($value));
    }

    private function opt(string $key): string
    {
        return trim((string) $this->settings->get($key, ''));
    }
}

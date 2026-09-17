<?php

namespace Plugins\Connect\Services;

use App\Models\Member\Member;
use App\Services\Video\VideoSettingService;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ConnectService
{
    public function __construct(private readonly VideoSettingService $settings) {}

    /** @return array{0:string,1:string} */
    public function pair(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return ['', ''];
        }
        if (str_contains($raw, ',')) {
            [$id, $secret] = array_map('trim', explode(',', $raw, 2));

            return [$id, $secret];
        }
        $parts = preg_split('/\s+/', $raw) ?: [];
        if (count($parts) >= 2) {
            return [trim((string) $parts[0]), trim((string) $parts[1])];
        }

        return ['', ''];
    }

    public function qqReady(): bool
    {
        [$id, $secret] = $this->pair($this->opt('oauth_qq'));

        return $id !== '' && $secret !== '';
    }

    public function wechatReady(): bool
    {
        [$id, $secret] = $this->wechatPair();

        return $id !== '' && $secret !== '';
    }

    public function qqAuthorizeUrl(string $state): string|array
    {
        [$id, $secret] = $this->pair($this->opt('oauth_qq'));
        if ($id === '' || $secret === '') {
            return Result::fail('未配置 QQ 登录参数');
        }

        return 'https://graph.qq.com/oauth2.0/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $id,
            'redirect_uri' => url('/connect/qq/callback'),
            'state' => $state,
            'scope' => 'get_user_info',
        ]);
    }

    public function wechatAuthorizeUrl(string $state): string|array
    {
        [$id, $secret] = $this->wechatPair();
        if ($id === '' || $secret === '') {
            return Result::fail('未配置微信登录参数');
        }

        return 'https://open.weixin.qq.com/connect/qrconnect?'.http_build_query([
            'appid' => $id,
            'redirect_uri' => url('/connect/wechat/callback'),
            'response_type' => 'code',
            'scope' => 'snsapi_login',
            'state' => $state,
        ]).'#wechat_redirect';
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function loginQq(string $code): array
    {
        [$id, $secret] = $this->pair($this->opt('oauth_qq'));
        if ($id === '' || $secret === '') {
            return Result::fail('未配置 QQ 登录参数');
        }
        if (trim($code) === '') {
            return Result::fail('缺少授权码');
        }
        try {
            $tokenRes = Http::timeout(15)->get('https://graph.qq.com/oauth2.0/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $id,
                'client_secret' => $secret,
                'code' => $code,
                'redirect_uri' => url('/connect/qq/callback'),
                'fmt' => 'json',
            ]);
        } catch (\Throwable $e) {
            return Result::fail($e->getMessage() !== '' ? $e->getMessage() : 'QQ 换票失败');
        }
        $token = $this->decodeQq($tokenRes->body());
        $access = (string) ($token['access_token'] ?? '');
        if ($access === '') {
            return Result::fail((string) ($token['error_description'] ?? $token['msg'] ?? 'QQ 换票失败'));
        }
        try {
            $openRes = Http::timeout(15)->get('https://graph.qq.com/oauth2.0/me', [
                'access_token' => $access,
                'fmt' => 'json',
            ]);
        } catch (\Throwable $e) {
            return Result::fail($e->getMessage() !== '' ? $e->getMessage() : 'QQ 取 OpenId 失败');
        }
        $open = $this->decodeQq($openRes->body());
        $openid = (string) ($open['openid'] ?? '');
        if ($openid === '') {
            return Result::fail((string) ($open['error_description'] ?? 'QQ 未返回 OpenId'));
        }
        $name = 'QQ用户';
        try {
            $infoRes = Http::timeout(15)->get('https://graph.qq.com/user/get_user_info', [
                'access_token' => $access,
                'oauth_consumer_key' => $id,
                'openid' => $openid,
            ]);
            $info = $infoRes->json();
            if (is_array($info) && trim((string) ($info['nickname'] ?? '')) !== '') {
                $name = mb_substr((string) $info['nickname'], 0, 40);
            }
        } catch (\Throwable) {
        }

        return $this->loginOrCreate('qq_openid', $openid, $name, 'qq_'.$openid.'@connect.local');
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function loginWechat(string $code): array
    {
        [$id, $secret] = $this->wechatPair();
        if ($id === '' || $secret === '') {
            return Result::fail('未配置微信登录参数');
        }
        if (trim($code) === '') {
            return Result::fail('缺少授权码');
        }
        try {
            $res = Http::timeout(15)->get('https://api.weixin.qq.com/sns/oauth2/access_token', [
                'appid' => $id,
                'secret' => $secret,
                'code' => $code,
                'grant_type' => 'authorization_code',
            ]);
        } catch (\Throwable $e) {
            return Result::fail($e->getMessage() !== '' ? $e->getMessage() : '微信换票失败');
        }
        $json = $res->json();
        if (! is_array($json) || empty($json['openid'])) {
            return Result::fail((string) ($json['errmsg'] ?? '微信换票失败'));
        }
        $openid = (string) $json['openid'];
        $name = '微信用户';
        $access = (string) ($json['access_token'] ?? '');
        if ($access !== '') {
            try {
                $info = Http::timeout(15)->get('https://api.weixin.qq.com/sns/userinfo', [
                    'access_token' => $access,
                    'openid' => $openid,
                ])->json();
                if (is_array($info) && trim((string) ($info['nickname'] ?? '')) !== '') {
                    $name = mb_substr((string) $info['nickname'], 0, 40);
                }
            } catch (\Throwable) {
            }
        }

        return $this->loginOrCreate('wechat_openid', $openid, $name, 'wx_'.$openid.'@connect.local');
    }

    /** @return array{0:string,1:string} */
    private function wechatPair(): array
    {
        [$id, $secret] = $this->pair($this->opt('oauth_wechat'));
        if ($id !== '' && $secret !== '') {
            return [$id, $secret];
        }
        $id = $this->opt('weixin_appid');
        $secret = $this->opt('weixin_secret');

        return [$id, $secret];
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    private function loginOrCreate(string $column, string $openid, string $name, string $email): array
    {
        if (! Schema::hasTable('members') || ! Schema::hasColumn('members', $column)) {
            return Result::fail('会员表还没有社交登录字段，请先迁移数据库');
        }
        $member = Member::query()->where($column, $openid)->first();
        if (! $member) {
            $now = time();
            $member = Member::query()->create([
                'name' => $name !== '' ? $name : '用户',
                'email' => $this->uniqueEmail($email),
                'password' => password_hash(Str::random(32), PASSWORD_DEFAULT),
                'status' => 1,
                'points' => 0,
                $column => $openid,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        if ((int) $member->status !== 1) {
            return Result::fail('账号已停用');
        }

        return Result::success(['id' => (int) $member->id], '登录成功');
    }

    private function uniqueEmail(string $email): string
    {
        if (! Member::query()->where('email', $email)->exists()) {
            return $email;
        }

        return Str::lower(Str::random(10)).'@connect.local';
    }

    /** @return array<string, mixed> */
    private function decodeQq(string $body): array
    {
        $body = trim($body);
        if (str_starts_with($body, 'callback(')) {
            $body = trim(substr($body, 9, -2));
        }
        $json = json_decode($body, true);
        if (is_array($json)) {
            return $json;
        }
        parse_str($body, $out);

        return is_array($out) ? $out : [];
    }

    private function opt(string $key): string
    {
        return trim((string) $this->settings->get($key, ''));
    }
}

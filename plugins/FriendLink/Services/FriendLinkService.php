<?php

namespace Plugins\FriendLink\Services;

use App\Support\Captcha;
use App\Support\Utils\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Plugins\FriendLink\Models\FriendLink;
use Plugins\FriendLink\Models\FriendLinkCate;
use Plugins\FriendLink\Models\FriendLinkClick;
use Plugins\FriendLink\Models\FriendLinkHit;
use Plugins\FriendLink\Models\FriendLinkOption;

class FriendLinkService
{
    public function ready(): bool
    {
        return Schema::hasTable('plugin_friend_links')
            && Schema::hasTable('plugin_friend_link_cates')
            && Schema::hasTable('plugin_friend_link_options');
    }

    /** @return array{mode:string,min_referer:int,allow_apply:int,allow_edit:int} */
    public function options(): array
    {
        $out = [
            'mode' => 'normal',
            'min_referer' => 1,
            'allow_apply' => 1,
            'allow_edit' => 1,
        ];
        if (! Schema::hasTable('plugin_friend_link_options')) {
            return $out;
        }
        $rows = FriendLinkOption::query()->pluck('v', 'k')->all();
        $mode = strtolower(trim((string) ($rows['mode'] ?? 'normal')));
        $out['mode'] = $mode === 'strong' ? 'strong' : 'normal';
        $out['min_referer'] = max(1, (int) ($rows['min_referer'] ?? 1));
        $out['allow_apply'] = (int) ($rows['allow_apply'] ?? 1) === 1 ? 1 : 0;
        $out['allow_edit'] = (int) ($rows['allow_edit'] ?? 1) === 1 ? 1 : 0;

        return $out;
    }

    public function setOption(string $k, string $v): void
    {
        if (! Schema::hasTable('plugin_friend_link_options')) {
            return;
        }
        $row = FriendLinkOption::query()->where('k', $k)->first();
        if ($row) {
            $row->v = $v;
            $row->save();

            return;
        }
        FriendLinkOption::query()->create(['k' => $k, 'v' => $v]);
    }

    /** @return Collection<int, FriendLink> */
    public function listed(): Collection
    {
        if (! $this->ready()) {
            return collect();
        }
        $q = FriendLink::query()->where('status', 1);
        if ($this->options()['mode'] === 'strong') {
            $q->orderByDesc('referer_total')->orderByDesc('sort')->orderByDesc('id');
        } else {
            $q->orderByDesc('sort')->orderByDesc('id');
        }

        return $q->get();
    }

    /** @return Collection<int, FriendLinkCate> */
    public function listedCates(): Collection
    {
        if (! Schema::hasTable('plugin_friend_link_cates')) {
            return collect();
        }

        return FriendLinkCate::query()->where('status', 1)->orderByDesc('sort')->orderBy('id')->get();
    }

    public static function httpUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '' || preg_match('/^\s*(javascript|data|vbscript):/i', $url) === 1) {
            return null;
        }
        if (preg_match('#^https?://#i', $url) !== 1) {
            return null;
        }
        $parts = parse_url($url);
        if (! is_array($parts) || trim((string) ($parts['host'] ?? '')) === '') {
            return null;
        }

        return $url;
    }

    public static function hostOf(string $url): string
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?: ''));
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }

    public function ownHost(): string
    {
        try {
            $host = strtolower((string) request()->getHost());
        } catch (\Throwable) {
            $host = '';
        }
        if ($host === '') {
            $host = strtolower((string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: ''));
        }
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }

    /** @param  array<string, mixed>  $data */
    public function apply(array $data): array
    {
        if (! $this->ready()) {
            return Result::fail('友链未启用');
        }
        $opt = $this->options();
        if ($opt['allow_apply'] !== 1) {
            return Result::fail('前台申请已关闭');
        }
        if (! Captcha::check(isset($data['captcha']) ? (string) $data['captcha'] : null)) {
            return Result::fail('验证码不对');
        }
        $name = mb_substr(trim((string) ($data['name'] ?? '')), 0, 120);
        $url = self::httpUrl((string) ($data['url'] ?? ''));
        if ($name === '') {
            return Result::fail('请填写名称');
        }
        if ($url === null) {
            return Result::fail('网址只接受 http 或 https');
        }
        $type = 'text';
        $now = time();
        $token = bin2hex(random_bytes(16));
        $memberId = 0;
        try {
            $member = Auth::guard('member')->user();
            if ($member) {
                $memberId = (int) $member->id;
            }
        } catch (\Throwable) {
        }
        $row = FriendLink::query()->create([
            'cate_id' => max(0, (int) ($data['cate_id'] ?? 0)),
            'name' => $name,
            'url' => $url,
            'logo' => '',
            'email' => mb_substr(trim((string) ($data['email'] ?? '')), 0, 120),
            'remark' => mb_substr(trim((string) ($data['remark'] ?? '')), 0, 255),
            'type' => $type,
            'status' => 0,
            'edit_token' => $token,
            'clicks' => 0,
            'referer_total' => 0,
            'referer_day' => 0,
            'referer_month' => 0,
            'referer_year' => 0,
            'day_key' => '',
            'month_key' => '',
            'year_key' => '',
            'last_referer_at' => 0,
            'sort' => 0,
            'member_id' => $memberId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return Result::success(['id' => (int) $row->id, 'edit_token' => $token], '已提交，等审核。请保存下面的修改令牌。');
    }

    /** @param  array<string, mixed>  $data */
    public function editByToken(string $token, array $data): array
    {
        if (! $this->ready()) {
            return Result::fail('友链未启用');
        }
        if ($this->options()['allow_edit'] !== 1) {
            return Result::fail('前台自助修改已关闭');
        }
        $token = strtolower(trim($token));
        if (preg_match('/^[a-f0-9]{32}$/', $token) !== 1) {
            return Result::fail('令牌无效');
        }
        $row = FriendLink::query()->where('edit_token', $token)->first();
        if (! $row) {
            return Result::fail('找不到这条友链');
        }
        $name = mb_substr(trim((string) ($data['name'] ?? $row->name)), 0, 120);
        $url = self::httpUrl((string) ($data['url'] ?? $row->url));
        if ($name === '') {
            return Result::fail('请填写名称');
        }
        if ($url === null) {
            return Result::fail('网址只接受 http 或 https');
        }
        $row->name = $name;
        $row->url = $url;
        $row->email = mb_substr(trim((string) ($data['email'] ?? $row->email)), 0, 120);
        $row->type = 'text';
        if (array_key_exists('cate_id', $data)) {
            $row->cate_id = max(0, (int) $data['cate_id']);
        }
        $row->updated_at = time();
        $row->save();

        return Result::success(['id' => (int) $row->id], '已保存');
    }

    public function findByToken(string $token): ?FriendLink
    {
        $token = strtolower(trim($token));
        if (preg_match('/^[a-f0-9]{32}$/', $token) !== 1 || ! $this->ready()) {
            return null;
        }

        return FriendLink::query()->where('edit_token', $token)->first();
    }

    public function go(int $id, string $ip, string $ua): array
    {
        if (! $this->ready()) {
            return Result::fail('友链未启用');
        }
        $row = FriendLink::query()->where('id', $id)->where('status', 1)->first();
        if (! $row) {
            return Result::fail('找不到这条友链');
        }
        $url = self::httpUrl((string) $row->url);
        if ($url === null) {
            return Result::fail('网址无效');
        }
        $row->clicks = (int) $row->clicks + 1;
        $row->updated_at = time();
        $row->save();
        if (Schema::hasTable('plugin_friend_link_clicks')) {
            FriendLinkClick::query()->create([
                'link_id' => (int) $row->id,
                'ip' => mb_substr($ip, 0, 45),
                'ua' => mb_substr($ua, 0, 255),
                'created_at' => time(),
            ]);
        }

        return Result::success(['url' => $url]);
    }

    public function recordHit(string $referer, string $ip, string $ua = ''): array
    {
        unset($ua);
        if (! $this->ready() || ! Schema::hasTable('plugin_friend_link_hits')) {
            return Result::success(['matched' => 0]);
        }
        $referer = self::httpUrl($referer);
        if ($referer === null) {
            return Result::success(['matched' => 0]);
        }
        $fromHost = self::hostOf($referer);
        if ($fromHost === '' || $fromHost === $this->ownHost()) {
            return Result::success(['matched' => 0, 'ignored' => 'own']);
        }
        $opt = $this->options();
        $dayKey = date('Ymd');
        $monthKey = date('Ym');
        $yearKey = date('Y');
        $now = time();
        $matched = 0;
        $links = FriendLink::query()->whereIn('status', [0, 1])->get();
        foreach ($links as $link) {
            $linkHost = self::hostOf((string) $link->url);
            if ($linkHost === '' || $linkHost !== $fromHost) {
                continue;
            }
            $exists = FriendLinkHit::query()
                ->where('link_id', (int) $link->id)
                ->where('ip', mb_substr($ip, 0, 45))
                ->where('day_key', $dayKey)
                ->exists();
            if ($exists) {
                continue;
            }
            FriendLinkHit::query()->create([
                'link_id' => (int) $link->id,
                'ip' => mb_substr($ip, 0, 45),
                'from_host' => $fromHost,
                'from_url' => mb_substr($referer, 0, 500),
                'day_key' => $dayKey,
                'created_at' => $now,
            ]);
            if ((string) $link->day_key !== $dayKey) {
                $link->referer_day = 0;
                $link->day_key = $dayKey;
            }
            if ((string) $link->month_key !== $monthKey) {
                $link->referer_month = 0;
                $link->month_key = $monthKey;
            }
            if ((string) $link->year_key !== $yearKey) {
                $link->referer_year = 0;
                $link->year_key = $yearKey;
            }
            $link->referer_day = (int) $link->referer_day + 1;
            $link->referer_month = (int) $link->referer_month + 1;
            $link->referer_year = (int) $link->referer_year + 1;
            $link->referer_total = (int) $link->referer_total + 1;
            $link->last_referer_at = $now;
            if (
                $opt['mode'] === 'strong'
                && (int) $link->status === 0
                && (int) $link->referer_total >= $opt['min_referer']
            ) {
                $link->status = 1;
            }
            $link->updated_at = $now;
            $link->save();
            $matched++;
        }

        return Result::success(['matched' => $matched]);
    }
}

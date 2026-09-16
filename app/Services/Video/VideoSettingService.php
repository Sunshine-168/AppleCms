<?php

namespace App\Services\Video;

use App\Models\Video\VideoOption;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class VideoSettingService
{
    public const CACHE_KEY = 'video.options.map';

    /** @return array<string, string> */
    public function all(): array
    {
        $defaults = [
            'site_title' => (string) config('video.site.title', config('app.name')),
            'site_keyword' => (string) config('video.site.keyword', ''),
            'site_description' => (string) config('video.site.description', ''),
            'html_cache_enabled' => (string) (int) config('video.html_cache.enabled', false),
            'html_cache_ttl' => (string) (int) config('video.html_cache.ttl', 3600),
            'rewrite_mode' => (string) config('video.rewrite.mode', 'laravel'),
            'rewrite_suffix' => (string) config('video.rewrite.suffix', '.html'),
            'baidu_push_token' => '',
            'shenma_push_token' => '',
            'bing_push_token' => '',
            'pay_wechat_mchid' => '',
            'pay_wechat_key' => '',
            'pay_alipay_appid' => '',
            'pay_alipay_key' => '',
            'storage_disk' => 'local',
            's3_key' => '',
            's3_secret' => '',
            's3_region' => 'us-east-1',
            's3_bucket' => '',
            's3_endpoint' => '',
            's3_url' => '',
            'icp' => '',
            'site_closed' => '0',
            'site_close_tip' => '站点维护中',
            'collect_in_status' => '1',
            'collect_sync_pic' => '1',
            'collect_hours' => '24',
            'inbound_key' => '',
            'member_register' => '1',
            'member_comment_login' => '0',
            'comment_audit' => '0',
            'gbook_audit' => '0',
            'trysee_seconds' => '0',
            'banned_words' => '',
            'seo_title_vod' => '{name} - {site}',
            'seo_title_type' => '{type} - {site}',
            'filter_area' => '大陆,香港,台湾,美国,韩国,日本',
            'filter_lang' => '国语,粤语,英语,韩语,日语',
            'filter_year' => '2026,2025,2024,2023,2022,2021,2020',
            'provide_key' => '',
            'app_key' => '',
            'collect_hits_min' => '0',
            'collect_hits_max' => '0',
            'collect_pic_local' => '0',
            'smtp_host' => '',
            'smtp_port' => '465',
            'smtp_user' => '',
            'smtp_pass' => '',
            'smtp_from' => '',
            'play_buffer' => '5',
            'play_encrypt' => '0',
            'danmaku_enabled' => '1',
            'danmaku_login' => '0',
            'collect_areawords' => '',
            'collect_langwords' => '',
        ];
        if (! $this->ready()) {
            return $defaults;
        }

        return Cache::remember(self::CACHE_KEY, 600, function () use ($defaults) {
            $rows = VideoOption::query()->pluck('v', 'k')->all();
            foreach ($defaults as $k => $v) {
                if (! array_key_exists($k, $rows) || $rows[$k] === null || $rows[$k] === '') {
                    $rows[$k] = $v;
                }
            }

            return $rows;
        });
    }

    public function get(string $key, mixed $default = ''): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function save(array $data): array
    {
        if (! $this->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $now = time();
        $keys = [
            'site_title', 'site_keyword', 'site_description', 'html_cache_enabled', 'html_cache_ttl',
            'rewrite_mode', 'rewrite_suffix', 'baidu_push_token', 'shenma_push_token', 'bing_push_token',
            'pay_wechat_mchid', 'pay_wechat_key', 'pay_alipay_appid', 'pay_alipay_key', 'storage_disk',
            's3_key', 's3_secret', 's3_region', 's3_bucket', 's3_endpoint', 's3_url',
            'icp', 'site_closed', 'site_close_tip', 'collect_in_status', 'collect_sync_pic', 'collect_hours',
            'inbound_key', 'member_register', 'member_comment_login', 'comment_audit', 'gbook_audit',
            'trysee_seconds', 'banned_words', 'seo_title_vod', 'seo_title_type', 'filter_area', 'filter_lang', 'filter_year',
            'provide_key', 'app_key', 'collect_hits_min', 'collect_hits_max', 'collect_pic_local',
            'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from',
            'play_buffer', 'play_encrypt', 'danmaku_enabled', 'danmaku_login', 'collect_areawords', 'collect_langwords',
        ];
        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $value = is_scalar($data[$key]) ? (string) $data[$key] : '';
            VideoOption::query()->updateOrCreate(
                ['k' => $key],
                ['v' => $value, 'updated_at' => $now]
            );
        }
        Cache::forget(self::CACHE_KEY);
        Cache::flush();
        $this->applyRuntime();

        return Result::success([], '已保存');
    }

    public function applyRuntime(): void
    {
        try {
            $all = $this->all();
        } catch (\Throwable) {
            return;
        }
        config([
            'video.rewrite.mode' => (string) ($all['rewrite_mode'] ?? config('video.rewrite.mode', 'laravel')),
            'video.rewrite.suffix' => (string) ($all['rewrite_suffix'] ?? config('video.rewrite.suffix', '.html')),
            'video.storage_disk' => (string) ($all['storage_disk'] ?? 'local'),
        ]);
        if (trim((string) ($all['smtp_host'] ?? '')) !== '') {
            $port = (int) ($all['smtp_port'] ?? 465);
            $encryption = match ($port) {
                465 => 'ssl',
                587 => 'tls',
                default => null,
            };
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => (string) ($all['smtp_host'] ?? ''),
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.username' => (string) ($all['smtp_user'] ?? ''),
                'mail.mailers.smtp.password' => (string) ($all['smtp_pass'] ?? ''),
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.from.address' => (string) ($all['smtp_from'] ?? ''),
                'mail.from.name' => (string) ($all['site_title'] ?? config('app.name')),
            ]);
        }
        if (($all['storage_disk'] ?? 'local') === 's3' && (string) ($all['s3_bucket'] ?? '') !== '') {
            config([
                'filesystems.disks.vod' => [
                    'driver' => 's3',
                    'key' => (string) ($all['s3_key'] ?? ''),
                    'secret' => (string) ($all['s3_secret'] ?? ''),
                    'region' => (string) ($all['s3_region'] ?? 'us-east-1'),
                    'bucket' => (string) ($all['s3_bucket'] ?? ''),
                    'url' => (string) ($all['s3_url'] ?? ''),
                    'endpoint' => (string) ($all['s3_endpoint'] ?? '') ?: null,
                    'use_path_style_endpoint' => true,
                    'throw' => false,
                ],
            ]);
        }
    }

    public function site(): array
    {
        $all = $this->all();

        return [
            'title' => (string) ($all['site_title'] ?? config('app.name')),
            'keyword' => (string) ($all['site_keyword'] ?? ''),
            'description' => (string) ($all['site_description'] ?? ''),
            'theme' => (string) config('video.theme', 'default'),
            'html_cache_enabled' => (int) ($all['html_cache_enabled'] ?? 0) === 1,
            'html_cache_ttl' => max(0, (int) ($all['html_cache_ttl'] ?? 3600)),
            'rewrite_mode' => (string) ($all['rewrite_mode'] ?? config('video.rewrite.mode', 'laravel')),
            'rewrite_suffix' => (string) ($all['rewrite_suffix'] ?? config('video.rewrite.suffix', '.html')),
            'baidu_push_token' => (string) ($all['baidu_push_token'] ?? ''),
            'shenma_push_token' => (string) ($all['shenma_push_token'] ?? ''),
            'bing_push_token' => (string) ($all['bing_push_token'] ?? ''),
            'pay_wechat_mchid' => (string) ($all['pay_wechat_mchid'] ?? ''),
            'pay_wechat_key' => (string) ($all['pay_wechat_key'] ?? ''),
            'pay_alipay_appid' => (string) ($all['pay_alipay_appid'] ?? ''),
            'pay_alipay_key' => (string) ($all['pay_alipay_key'] ?? ''),
            'storage_disk' => (string) ($all['storage_disk'] ?? 'local'),
            's3_key' => (string) ($all['s3_key'] ?? ''),
            's3_secret' => (string) ($all['s3_secret'] ?? ''),
            's3_region' => (string) ($all['s3_region'] ?? ''),
            's3_bucket' => (string) ($all['s3_bucket'] ?? ''),
            's3_endpoint' => (string) ($all['s3_endpoint'] ?? ''),
            's3_url' => (string) ($all['s3_url'] ?? ''),
            'icp' => (string) ($all['icp'] ?? ''),
            'site_closed' => (string) ($all['site_closed'] ?? '0'),
            'site_close_tip' => (string) ($all['site_close_tip'] ?? ''),
            'collect_in_status' => (string) ($all['collect_in_status'] ?? '1'),
            'collect_sync_pic' => (string) ($all['collect_sync_pic'] ?? '1'),
            'collect_hours' => (string) ($all['collect_hours'] ?? '24'),
            'inbound_key' => (string) ($all['inbound_key'] ?? ''),
            'member_register' => (string) ($all['member_register'] ?? '1'),
            'member_comment_login' => (string) ($all['member_comment_login'] ?? '0'),
            'comment_audit' => (string) ($all['comment_audit'] ?? '0'),
            'gbook_audit' => (string) ($all['gbook_audit'] ?? '0'),
            'trysee_seconds' => (string) ($all['trysee_seconds'] ?? '0'),
            'banned_words' => (string) ($all['banned_words'] ?? ''),
            'seo_title_vod' => (string) ($all['seo_title_vod'] ?? ''),
            'seo_title_type' => (string) ($all['seo_title_type'] ?? ''),
            'filter_area' => (string) ($all['filter_area'] ?? ''),
            'filter_lang' => (string) ($all['filter_lang'] ?? ''),
            'filter_year' => (string) ($all['filter_year'] ?? ''),
            'provide_key' => (string) ($all['provide_key'] ?? ''),
            'collect_hits_min' => (string) ($all['collect_hits_min'] ?? '0'),
            'collect_hits_max' => (string) ($all['collect_hits_max'] ?? '0'),
            'collect_pic_local' => (string) ($all['collect_pic_local'] ?? '0'),
            'smtp_host' => (string) ($all['smtp_host'] ?? ''),
            'smtp_port' => (string) ($all['smtp_port'] ?? '465'),
            'smtp_user' => (string) ($all['smtp_user'] ?? ''),
            'smtp_pass' => (string) ($all['smtp_pass'] ?? ''),
            'smtp_from' => (string) ($all['smtp_from'] ?? ''),
            'play_buffer' => (string) ($all['play_buffer'] ?? '5'),
            'play_encrypt' => (string) ($all['play_encrypt'] ?? '0'),
            'collect_areawords' => (string) ($all['collect_areawords'] ?? ''),
            'collect_langwords' => (string) ($all['collect_langwords'] ?? ''),
        ];
    }

    private function ready(): bool
    {
        try {
            return Schema::hasTable('video_options');
        } catch (\Throwable) {
            return false;
        }
    }
}

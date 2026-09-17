<?php

namespace App\Services\Video;

use App\Models\System\SysUserLogModel;
use App\Models\Video\VideoOption;
use App\Support\AdminIpAllowlist;
use App\Support\AdminOpLog;
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
            'disk_html_enabled' => (string) (int) config('video.disk_html.enabled', false),
            'rewrite_mode' => (string) config('video.rewrite.mode', 'laravel'),
            'rewrite_suffix' => (string) config('video.rewrite.suffix', '.html'),
            'baidu_push_token' => '',
            'shenma_push_token' => '',
            'bing_push_token' => '',
            'pay_wechat_appid' => '',
            'pay_wechat_mchid' => '',
            'pay_wechat_key' => '',
            'pay_alipay_appid' => '',
            'pay_alipay_key' => '',
            'pay_alipay_public' => '',
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
            'collect_to_temp' => '0',
            'admin_ip_allow' => '',
            'weixin_appid' => '',
            'weixin_secret' => '',
            'weixin_token' => '',
            'sms_provider' => '',
            'sms_key' => '',
            'sms_secret' => '',
            'sms_sign' => '',
            'sms_tpl_code' => '',
            'oauth_qq' => '',
            'oauth_wechat' => '',
            'oauth_weibo' => '',
            'theme_primary' => '',
            'theme_logo' => '',
            'watermark_text' => '',
            'analytics_code' => '',
            'seo_title_play' => '{name} 在线播放 - {site}',
            'member_invite' => '0',
            'upload_ext' => 'jpg,png,gif,webp,mp4',
            'upload_max_mb' => '8',
            'ai_provider' => '',
            'ai_key' => '',
            'ai_model' => '',
            'ai_endpoint' => '',
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
            'site_title', 'site_keyword', 'site_description', 'html_cache_enabled', 'html_cache_ttl', 'disk_html_enabled',
            'rewrite_mode', 'rewrite_suffix', 'baidu_push_token', 'shenma_push_token', 'bing_push_token',
            'pay_wechat_appid', 'pay_wechat_mchid', 'pay_wechat_key', 'pay_alipay_appid', 'pay_alipay_key', 'pay_alipay_public', 'storage_disk',
            's3_key', 's3_secret', 's3_region', 's3_bucket', 's3_endpoint', 's3_url',
            'icp', 'site_closed', 'site_close_tip', 'collect_in_status', 'collect_sync_pic', 'collect_hours',
            'inbound_key', 'member_register', 'member_comment_login', 'comment_audit', 'gbook_audit',
            'trysee_seconds', 'banned_words', 'seo_title_vod', 'seo_title_type', 'filter_area', 'filter_lang', 'filter_year',
            'provide_key', 'app_key', 'collect_hits_min', 'collect_hits_max', 'collect_pic_local',
            'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from',
            'play_buffer', 'play_encrypt', 'danmaku_enabled', 'danmaku_login', 'collect_areawords', 'collect_langwords',
            'collect_to_temp', 'admin_ip_allow', 'weixin_appid', 'weixin_secret', 'weixin_token',
            'sms_provider', 'sms_key', 'sms_secret', 'sms_sign', 'sms_tpl_code', 'oauth_qq', 'oauth_wechat', 'oauth_weibo',
            'theme_primary', 'theme_logo', 'watermark_text', 'analytics_code', 'seo_title_play',
            'member_invite', 'upload_ext', 'upload_max_mb', 'ai_provider', 'ai_key', 'ai_model', 'ai_endpoint',
        ];
        try {
            foreach (app(\App\Support\Plugins\PluginHost::class)->extraPages() as $page) {
                foreach ($page['fields'] ?? [] as $field) {
                    if (! empty($field['name'])) {
                        $keys[] = (string) $field['name'];
                    }
                }
            }
        } catch (\Throwable) {
        }
        $keys = array_values(array_unique($keys));
        if (array_key_exists('admin_ip_allow', $data)) {
            $checked = $this->prepareAdminIpAllow((string) $data['admin_ip_allow']);
            if ((int) ($checked['code'] ?? 1) !== 0) {
                return $checked;
            }
            $data['admin_ip_allow'] = (string) ($checked['data']['value'] ?? '');
        }
        $keepIfBlank = ['smtp_pass', 's3_secret', 'ai_key', 'pay_wechat_key', 'pay_alipay_key', 'sms_secret', 'weixin_secret'];
        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $value = is_scalar($data[$key]) ? (string) $data[$key] : '';
            if (in_array($key, $keepIfBlank, true) && $value === '') {
                continue;
            }
            VideoOption::query()->updateOrCreate(
                ['k' => $key],
                ['v' => $value, 'updated_at' => $now]
            );
        }
        Cache::forget(self::CACHE_KEY);
        Cache::flush();
        $this->applyRuntime();

        $tab = trim((string) ($data['tab'] ?? ''));
        $summary = match (true) {
            $tab === 'look' => '改了站点外观设置',
            $tab === 'interact' => '改了站点互动设置',
            $tab === 'more' => '改了站点更多设置',
            array_key_exists('admin_ip_allow', $data) && $tab === '' => ((string) ($data['admin_ip_allow'] ?? '')) === ''
                ? '关闭了后台 IP 白名单'
                : '改了后台 IP 白名单',
            array_key_exists('provide_key', $data) && $tab === '' => trim((string) ($data['provide_key'] ?? '')) === ''
                ? '关掉了开放 API 密钥'
                : '改了开放 API 密钥',
            array_key_exists('inbound_key', $data) && $tab === '' => trim((string) ($data['inbound_key'] ?? '')) === ''
                ? '清掉了入库接口密钥'
                : '改了入库接口密钥',
            array_key_exists('baidu_push_token', $data) && $tab === '' => '改了搜索推送 Token',
            default => '改了站点设置',
        };

        return AdminOpLog::ifOk(Result::success([], '已保存'), 'save', $summary, [
            'module' => '站点设置',
            'target_type' => 'settings',
            'payload' => ['tab' => $tab],
        ]);
    }

    /** 只改列出的项，不清整站缓存（给静态生成工作台用） */
    public function saveOptions(array $data): array
    {
        if (! $this->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $now = time();
        foreach ($data as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }
            VideoOption::query()->updateOrCreate(
                ['k' => $key],
                ['v' => is_scalar($value) ? (string) $value : '', 'updated_at' => $now]
            );
        }
        Cache::forget(self::CACHE_KEY);
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

        return array_merge($all, [
            'title' => (string) ($all['site_title'] ?? config('app.name')),
            'keyword' => (string) ($all['site_keyword'] ?? ''),
            'description' => (string) ($all['site_description'] ?? ''),
            'theme' => (string) config('video.theme', 'default'),
            'html_cache_enabled' => (int) ($all['html_cache_enabled'] ?? 0) === 1,
            'html_cache_ttl' => max(0, (int) ($all['html_cache_ttl'] ?? 3600)),
            'disk_html_enabled' => (int) ($all['disk_html_enabled'] ?? 0) === 1,
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
            'collect_to_temp' => (string) ($all['collect_to_temp'] ?? '0'),
            'admin_ip_allow' => (string) ($all['admin_ip_allow'] ?? ''),
            'weixin_appid' => (string) ($all['weixin_appid'] ?? ''),
            'weixin_secret' => (string) ($all['weixin_secret'] ?? ''),
            'weixin_token' => (string) ($all['weixin_token'] ?? ''),
            'sms_provider' => (string) ($all['sms_provider'] ?? ''),
            'sms_key' => (string) ($all['sms_key'] ?? ''),
            'sms_secret' => (string) ($all['sms_secret'] ?? ''),
            'sms_sign' => (string) ($all['sms_sign'] ?? ''),
            'oauth_qq' => (string) ($all['oauth_qq'] ?? ''),
            'oauth_wechat' => (string) ($all['oauth_wechat'] ?? ''),
            'oauth_weibo' => (string) ($all['oauth_weibo'] ?? ''),
            'theme_primary' => (string) ($all['theme_primary'] ?? ''),
            'theme_logo' => (string) ($all['theme_logo'] ?? ''),
            'watermark_text' => (string) ($all['watermark_text'] ?? ''),
            'analytics_code' => (string) ($all['analytics_code'] ?? ''),
            'seo_title_play' => (string) ($all['seo_title_play'] ?? ''),
            'member_invite' => (string) ($all['member_invite'] ?? '0'),
            'upload_ext' => (string) ($all['upload_ext'] ?? ''),
            'upload_max_mb' => (string) ($all['upload_max_mb'] ?? '8'),
            'app_key' => (string) ($all['app_key'] ?? ''),
            'danmaku_enabled' => (string) ($all['danmaku_enabled'] ?? '1'),
            'danmaku_login' => (string) ($all['danmaku_login'] ?? '0'),
        ]);
    }

    /** @return array<string, array{title:string,fields:list<array<string,mixed>>}> */
    public function extraPages(): array
    {
        $core = [
            'theme' => [
                'title' => '主题参数',
                'fields' => [
                    ['name' => 'theme_logo', 'label' => 'Logo 地址', 'type' => 'text'],
                    ['name' => 'theme_primary', 'label' => '主色', 'type' => 'text', 'placeholder' => '#1e9fff'],
                ],
            ],
            'watermark' => [
                'title' => '图片水印',
                'fields' => [
                    ['name' => 'watermark_text', 'label' => '水印文字', 'type' => 'text', 'placeholder' => '本地化封面时写入右下角'],
                ],
            ],
            'analytics' => [
                'title' => '统计代码',
                'fields' => [
                    ['name' => 'analytics_code', 'label' => '统计脚本', 'type' => 'textarea', 'placeholder' => '百度/CNZZ 等粘贴到页脚'],
                ],
            ],
            'seo' => [
                'title' => 'SEO 标题',
                'fields' => [
                    ['name' => 'seo_title_vod', 'label' => '详情标题', 'type' => 'text'],
                    ['name' => 'seo_title_type', 'label' => '分类标题', 'type' => 'text'],
                    ['name' => 'seo_title_play', 'label' => '播放标题', 'type' => 'text'],
                ],
            ],
            'user' => [
                'title' => '会员参数',
                'fields' => [
                    ['name' => 'member_register', 'label' => '开放注册', 'type' => 'select', 'options' => ['1' => '是', '0' => '否']],
                    ['name' => 'member_invite', 'label' => '邀请码必填', 'type' => 'select', 'options' => ['0' => '否', '1' => '是']],
                    ['name' => 'trysee_seconds', 'label' => '试看秒数', 'type' => 'text'],
                ],
            ],
            'upload' => [
                'title' => '上传限制',
                'fields' => [
                    ['name' => 'upload_ext', 'label' => '扩展名', 'type' => 'text'],
                    ['name' => 'upload_max_mb', 'label' => '最大 MB', 'type' => 'text'],
                ],
            ],
            'comment' => [
                'title' => '评论留言',
                'fields' => [
                    ['name' => 'comment_audit', 'label' => '评论审核', 'type' => 'select', 'options' => ['0' => '否', '1' => '是']],
                    ['name' => 'gbook_audit', 'label' => '留言审核', 'type' => 'select', 'options' => ['0' => '否', '1' => '是']],
                    ['name' => 'member_comment_login', 'label' => '评论需登录', 'type' => 'select', 'options' => ['0' => '否', '1' => '是']],
                    ['name' => 'banned_words', 'label' => '屏蔽词', 'type' => 'textarea'],
                ],
            ],
            'url' => [
                'title' => 'URL 规则',
                'fields' => [
                    ['name' => 'rewrite_mode', 'label' => '链接怎么写', 'type' => 'select', 'options' => ['laravel' => '本站路由 /vod/123', 'mac' => '苹果风格 /index.php/vod/detail/id/123.html']],
                    ['name' => 'rewrite_suffix', 'label' => '后缀', 'type' => 'text'],
                ],
            ],
        ];
        try {
            return array_merge($core, app(\App\Support\Plugins\PluginHost::class)->extraPages());
        } catch (\Throwable) {
            return $core;
        }
    }

    /**
     * @return array{
     *     site: array<string, mixed>,
     *     current_ip: string,
     *     forwarded_ip: string,
     *     local: bool,
     *     enabled: bool,
     *     rules: list<string>,
     *     current_ok: bool,
     *     recent: list<string>
     * }
     */
    public function ipPage(): array
    {
        $site = $this->site();
        $raw = (string) ($site['admin_ip_allow'] ?? '');
        $rules = AdminIpAllowlist::parse($raw);
        $ip = trim((string) request()->ip());
        $forwarded = '';
        $xff = trim((string) request()->header('x-forwarded-for', ''));
        if ($xff !== '') {
            $first = trim(explode(',', $xff)[0] ?? '');
            if (filter_var($first, FILTER_VALIDATE_IP) && $first !== $ip) {
                $forwarded = $first;
            }
        }

        return [
            'site' => $site,
            'current_ip' => $ip,
            'forwarded_ip' => $forwarded,
            'local' => AdminIpAllowlist::isLocal($ip),
            'enabled' => $rules !== [],
            'rules' => $rules,
            'current_ok' => AdminIpAllowlist::allows($ip, $rules),
            'recent' => $this->recentAdminIps($ip, $rules),
        ];
    }

    /**
     * @return array{
     *     site: array<string, mixed>,
     *     has_key: bool,
     *     app_key_set: bool,
     *     video_count: int,
     *     provide_url: string,
     *     app_url: string
     * }
     */
    public function apiPage(): array
    {
        $site = $this->site();
        $count = 0;
        try {
            if (Schema::hasTable('videos')) {
                $count = (int) \App\Models\Video\VideoModel::query()->published()->count();
            }
        } catch (\Throwable) {
            $count = 0;
        }

        return [
            'site' => $site,
            'has_key' => trim((string) ($site['provide_key'] ?? '')) !== '',
            'app_key_set' => trim((string) ($site['app_key'] ?? '')) !== '',
            'video_count' => $count,
            'provide_url' => url('/api/provide/vod'),
            'app_url' => url('/api/app/vod'),
        ];
    }

    /**
     * @param  list<string>  $rules
     * @return list<string>
     */
    protected function recentAdminIps(string $current, array $rules): array
    {
        $out = [];
        $seen = [strtolower($current) => true];
        foreach ($rules as $rule) {
            $seen[strtolower($rule)] = true;
        }
        try {
            if (! Schema::hasTable('sys_user_log')) {
                return [];
            }
            $rows = SysUserLogModel::query()->orderByDesc('id')->limit(40)->get(['login_ip']);
        } catch (\Throwable) {
            return [];
        }
        foreach ($rows as $row) {
            $ip = trim((string) ($row->login_ip ?? ''));
            $key = strtolower($ip);
            if ($ip === '' || isset($seen[$key]) || ! AdminIpAllowlist::isValid($ip)) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $ip;
            if (count($out) >= 8) {
                break;
            }
        }

        return $out;
    }

    /** @return array{code:int,msg:string,data?:array{value:string}} */
    protected function prepareAdminIpAllow(string $raw): array
    {
        $rules = AdminIpAllowlist::parse($raw);
        $bad = [];
        foreach ($rules as $rule) {
            if (! AdminIpAllowlist::isValid($rule)) {
                $bad[] = $rule;
            }
        }
        if ($bad !== []) {
            return Result::fail('名单里有无法识别的项：'.implode('、', array_slice($bad, 0, 5)).'。只接受 IP 或网段，例如 203.0.113.8 或 192.168.1.0/24。留空表示不限制。');
        }
        if ($rules !== []) {
            $ip = trim((string) request()->ip());
            if (! AdminIpAllowlist::allows($ip, $rules)) {
                return Result::fail('当前访问 IP '.$ip.' 不在名单里。保存后你会被挡在后台外。请先点「加入当前 IP」再保存。');
            }
        }

        return Result::success(['value' => implode("\n", $rules)]);
    }

    /** @return array{site: array<string, mixed>, has_key: bool, key_tail: string, empty_n: int, provider_kind: string} */
    public function aiPage(): array
    {
        $site = $this->site();
        $key = trim((string) ($site['ai_key'] ?? ''));
        $provider = trim((string) ($site['ai_provider'] ?? ''));
        $empty = 0;
        try {
            if (Schema::hasTable('videos')) {
                $empty = (int) \App\Models\Video\VideoModel::query()->where(function ($q) {
                    $q->whereNull('description')->orWhere('description', '');
                })->count();
            }
        } catch (\Throwable) {
            $empty = 0;
        }

        return [
            'site' => $site,
            'has_key' => $key !== '',
            'key_tail' => $key !== '' ? substr($key, -4) : '',
            'empty_n' => $empty,
            'provider_kind' => $this->aiProviderKind($provider),
        ];
    }

    public function aiProviderKind(string $provider): string
    {
        $p = strtolower(trim($provider));
        if ($p === '') {
            return '';
        }
        if (in_array($p, ['openai', 'gpt', 'chatgpt', 'azure'], true) || str_contains($p, 'openai')) {
            return 'openai';
        }
        if (in_array($p, ['qwen', 'tongyi', 'dashscope', '通义'], true) || str_contains($provider, '通义')) {
            return 'qwen';
        }
        if (in_array($p, ['ernie', 'wenxin', 'baidu', '文心'], true) || str_contains($provider, '文心')) {
            return 'ernie';
        }

        return 'other';
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

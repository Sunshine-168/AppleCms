<?php

namespace App\Support;

use App\Plugins\PluginHost;

class AdminNav
{
    /**
     * 侧栏：只放日常会点的入口。
     */
    public static function groups(): array
    {
        $groups = [
            [
                'header' => 'nav.overview',
                'items' => [
                    ['url' => '/admin/welcome', 'icon' => 'tachometer-alt', 'label' => 'nav.dashboard'],
                    ['url' => '/admin/stats', 'icon' => 'chart-line', 'label' => 'nav.stats'],
                ],
            ],
            [
                'header' => 'nav.content',
                'items' => [
                    ['url' => '/admin/video', 'icon' => 'video', 'label' => 'nav.videos'],
                    ['url' => '/admin/video/types', 'icon' => 'sitemap', 'label' => 'nav.types'],
                    ['url' => '/admin/video/collects', 'icon' => 'cloud-download-alt', 'label' => 'nav.collects'],
                    ['url' => '/admin/video/comments', 'icon' => 'comments', 'label' => 'nav.comments'],
                ],
                'fold' => [
                    'label' => 'nav.more',
                    'items' => [
                        ['url' => '/admin/video/topics', 'icon' => 'layer-group', 'label' => 'nav.topics'],
                        ['url' => '/admin/video/actors', 'icon' => 'user-friends', 'label' => 'nav.actors'],
                        ['url' => '/admin/video/tags', 'icon' => 'tags', 'label' => 'nav.tags'],
                        ['url' => '/admin/video/arts', 'icon' => 'file-alt', 'label' => 'nav.arts'],
                        ['url' => '/admin/video/slides', 'icon' => 'images', 'label' => 'nav.slides'],
                        ['url' => '/admin/video/tools/recycle', 'icon' => 'trash-alt', 'label' => 'nav.recycle'],
                    ],
                ],
            ],
            [
                'header' => 'nav.users',
                'items' => [
                    ['url' => '/admin/video/members', 'icon' => 'users', 'label' => 'nav.members'],
                    ['url' => '/admin/video/orders', 'icon' => 'file-invoice', 'label' => 'nav.orders'],
                ],
                'fold' => [
                    'label' => 'nav.more',
                    'items' => [
                        ['url' => '/admin/video/groups', 'icon' => 'user-tag', 'label' => 'nav.groups'],
                        ['url' => '/admin/video/cards', 'icon' => 'credit-card', 'label' => 'nav.cards'],
                        ['url' => '/admin/video/plogs', 'icon' => 'coins', 'label' => 'nav.plogs'],
                    ],
                ],
            ],
            [
                'header' => 'nav.site',
                'items' => [
                    ['url' => '/admin/video/settings', 'icon' => 'cog', 'label' => 'nav.settings'],
                    ['url' => '/admin/video/templates', 'icon' => 'palette', 'label' => 'nav.templates'],
                    ['url' => '/admin/video/ads', 'icon' => 'bullhorn', 'label' => 'nav.ads'],
                ],
                'fold' => [
                    'label' => 'nav.more',
                    'items' => [
                        ['url' => '/admin/video/links', 'icon' => 'link', 'label' => 'nav.links'],
                        ['url' => '/admin/video/players', 'icon' => 'play-circle', 'label' => 'nav.players'],
                        ['url' => '/admin/video/make', 'icon' => 'bolt', 'label' => 'nav.make'],
                    ],
                ],
            ],
            [
                'header' => 'nav.system',
                'items' => [
                    ['url' => '/admin/user', 'icon' => 'user-cog', 'label' => 'nav.admins'],
                    ['url' => '/admin/system/roles', 'icon' => 'user-shield', 'label' => 'nav.roles'],
                    ['url' => '/admin/plugins', 'icon' => 'puzzle-piece', 'label' => 'nav.plugins'],
                    ['url' => '/admin/system/monitor/login-logs', 'icon' => 'history', 'label' => 'nav.logs'],
                    ['url' => '/admin/more', 'icon' => 'th-large', 'label' => 'nav.catalog', 'force' => true],
                ],
            ],
        ];
        $host = self::host();
        $map = [
            'nav.content' => 'content',
            'nav.users' => 'users',
            'nav.site' => 'site',
            'nav.system' => 'system',
        ];
        foreach ($groups as &$group) {
            $key = $map[$group['header']] ?? '';
            if ($key === '' || empty($group['fold'])) {
                continue;
            }
            $group['fold']['items'] = array_merge($group['fold']['items'], $host->sidebarFoldItems($key));
        }
        unset($group);

        return $groups;
    }

    public static function activeUrl(?string $path = null): string
    {
        $path = $path ?? ('/'.ltrim((string) request()->path(), '/'));
        $best = '';
        foreach (self::groups() as $group) {
            $items = array_merge($group['items'] ?? [], $group['fold']['items'] ?? []);
            foreach ($items as $item) {
                $href = explode('?', explode('#', (string) ($item['url'] ?? ''))[0])[0];
                if ($href === '') {
                    continue;
                }
                $hit = $path === $href || str_starts_with($path, $href.'/');
                if ($hit && strlen($href) > strlen($best)) {
                    $best = $href;
                }
            }
        }

        return $best;
    }

    public static function foldOpen(array $group, string $activeUrl): bool
    {
        foreach ($group['fold']['items'] ?? [] as $item) {
            if ((string) ($item['url'] ?? '') === $activeUrl) {
                return true;
            }
        }

        return false;
    }

    /**
     * 低频入口：放目录页，不进侧栏。
     */
    public static function catalog(): array
    {
        $blocks = [
            [
                'title' => 'more.collect',
                'hint' => 'more.collect_hint',
                'items' => [
                    ['url' => '/admin/video/collect_logs', 'label' => 'item.collect_logs'],
                    ['url' => '/admin/video/collect_tasks', 'label' => 'item.collect_tasks'],
                    ['url' => '/admin/video/unions', 'label' => 'item.unions'],
                    ['url' => '/admin/video/audits', 'label' => 'item.audits'],
                    ['url' => '/admin/video/tools/hub', 'label' => 'item.collect_hub'],
                    ['url' => '/admin/video/collect_temps', 'label' => 'item.collect_temps'],
                    ['url' => '/admin/video/config/collect', 'label' => 'item.config_collect'],
                    ['url' => '/admin/video/config/interface', 'label' => 'item.config_interface'],
                ],
            ],
            [
                'title' => 'more.complete',
                'hint' => 'more.complete_hint',
                'items' => [
                    ['url' => '/admin/video/roles', 'label' => 'item.roles'],
                    ['url' => '/admin/video/plots', 'label' => 'item.plots'],
                    ['url' => '/admin/video/websites', 'label' => 'item.websites'],
                    ['url' => '/admin/video/classes', 'label' => 'item.classes'],
                    ['url' => '/admin/video/synonyms', 'label' => 'item.synonyms'],
                    ['url' => '/admin/video/searchwords', 'label' => 'item.searchwords'],
                    ['url' => '/admin/video/wizard', 'label' => 'item.wizard'],
                    ['url' => '/admin/video/downloaders', 'label' => 'item.downloaders'],
                    ['url' => '/admin/video/servers', 'label' => 'item.servers'],
                    ['url' => '/admin/video/tools/quality', 'label' => 'item.quality'],
                    ['url' => '/admin/video/tools/images', 'label' => 'item.images'],
                    ['url' => '/admin/video/tools/players', 'label' => 'item.batch_players'],
                ],
            ],
            [
                'title' => 'more.interact',
                'items' => [
                    ['url' => '/admin/video/reports', 'label' => 'item.reports'],
                    ['url' => '/admin/video/guestbooks', 'label' => 'item.guestbooks'],
                    ['url' => '/admin/video/playfails', 'label' => 'item.playfails'],
                    ['url' => '/admin/video/pms', 'label' => 'item.pms'],
                    ['url' => '/admin/video/notifies', 'label' => 'item.notifies'],
                ],
            ],
            [
                'title' => 'more.member',
                'items' => [
                    ['url' => '/admin/video/withdraws', 'label' => 'item.withdraws'],
                    ['url' => '/admin/video/invites', 'label' => 'item.invites'],
                    ['url' => '/admin/video/favorites', 'label' => 'item.favorites'],
                    ['url' => '/admin/video/config/user', 'label' => 'item.config_user'],
                ],
            ],
            [
                'title' => 'more.site',
                'items' => [
                    ['url' => '/admin/video/config/seo', 'label' => 'item.config_seo'],
                    ['url' => '/admin/video/config/theme', 'label' => 'item.config_theme'],
                    ['url' => '/admin/video/config/player', 'label' => 'item.config_player'],
                    ['url' => '/admin/video/config/url', 'label' => 'item.config_url'],
                    ['url' => '/admin/video/rewrite', 'label' => 'item.rewrite'],
                    ['url' => '/admin/video/config/upload', 'label' => 'item.config_upload'],
                    ['url' => '/admin/video/config/comment', 'label' => 'item.config_comment'],
                    ['url' => '/admin/video/config/email', 'label' => 'item.config_email'],
                    ['url' => '/admin/video/config/watermark', 'label' => 'item.config_watermark'],
                    ['url' => '/admin/video/config/analytics', 'label' => 'item.config_analytics'],
                    ['url' => '/admin/video/domains', 'label' => 'item.domains'],
                    ['url' => '/admin/video/push', 'label' => 'item.push'],
                    ['url' => '/admin/stats', 'label' => 'item.stats'],
                    ['url' => '/admin/video/ulogs', 'label' => 'item.ulogs'],
                ],
            ],
            [
                'title' => 'more.security',
                'items' => [
                    ['url' => '/admin/video/config/api', 'label' => 'item.config_api'],
                    ['url' => '/admin/video/apidoc', 'label' => 'item.apidoc'],
                    ['url' => '/admin/video/config/ip', 'label' => 'item.config_ip'],
                    ['url' => '/admin/video/safety', 'label' => 'item.safety'],
                    ['url' => '/admin/video/accesslogs', 'label' => 'item.accesslogs'],
                    ['url' => '/admin/video/botlogs', 'label' => 'item.botlogs'],
                ],
            ],
            [
                'title' => 'more.system',
                'items' => [
                    ['url' => '/admin/plugins', 'label' => 'item.plugins'],
                    ['url' => '/admin/system/menus', 'label' => 'item.menus'],
                    ['url' => '/admin/system/dicts', 'label' => 'item.dicts'],
                    ['url' => '/admin/system/attachments', 'label' => 'item.attachments'],
                    ['url' => '/admin/video/tools/annex', 'label' => 'item.annex'],
                    ['url' => '/admin/system/tools/cache', 'label' => 'item.cache'],
                    ['url' => '/admin/system/tools/schedule', 'label' => 'item.schedule'],
                    ['url' => '/admin/system/monitor/operate-logs', 'label' => 'item.operate_logs'],
                    ['url' => '/admin/system/monitor/system-logs', 'label' => 'item.system_logs'],
                    ['url' => '/admin/system/database/backup', 'label' => 'item.db_backup'],
                    ['url' => '/admin/system/database/restore', 'label' => 'item.db_restore'],
                    ['url' => '/admin/system/database/dict', 'label' => 'item.db_dict'],
                    ['url' => '/admin/system/database/sql', 'label' => 'item.db_sql'],
                    ['url' => '/admin/system/database/replace', 'label' => 'item.db_replace'],
                    ['url' => '/admin/system/shortcut', 'label' => 'item.shortcut'],
                ],
            ],
        ];
        $host = self::host();
        foreach ($blocks as &$block) {
            $block['items'] = array_merge($block['items'], $host->catalogItems($block['title']));
        }
        unset($block);

        return $blocks;
    }

    private static function host(): PluginHost
    {
        try {
            return app(PluginHost::class);
        } catch (\Throwable) {
            return new PluginHost();
        }
    }
}

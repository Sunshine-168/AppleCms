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
                'id' => 'collect',
                'title' => 'more.collect',
                'hint' => 'more.collect_hint',
                'items' => [
                    self::cat('/admin/video/collect_logs', 'item.collect_logs', 'hint.collect_logs'),
                    self::cat('/admin/video/collect_tasks', 'item.collect_tasks', 'hint.collect_tasks'),
                    self::cat('/admin/video/unions', 'item.unions', 'hint.unions'),
                    self::cat('/admin/video/audits', 'item.audits', 'hint.audits'),
                    self::cat('/admin/video/tools/hub', 'item.collect_hub', 'hint.collect_hub'),
                    self::cat('/admin/video/collect_temps', 'item.collect_temps', 'hint.collect_temps'),
                    self::cat('/admin/video/config/collect', 'item.config_collect', 'hint.config_collect'),
                    self::cat('/admin/video/config/interface', 'item.config_interface', 'hint.config_interface'),
                ],
            ],
            [
                'id' => 'complete',
                'title' => 'more.complete',
                'hint' => 'more.complete_hint',
                'items' => [
                    self::cat('/admin/video/searchwords', 'item.searchwords', 'hint.searchwords'),
                    self::cat('/admin/video/tools/images', 'item.images', 'hint.images'),
                    self::cat('/admin/video/tools/players', 'item.batch_players', 'hint.batch_players'),
                ],
            ],
            [
                'id' => 'interact',
                'title' => 'more.interact',
                'hint' => 'more.interact_hint',
                'items' => [
                    self::cat('/admin/video/reports', 'item.reports', 'hint.reports'),
                    self::cat('/admin/video/guestbooks', 'item.guestbooks', 'hint.guestbooks'),
                    self::cat('/admin/video/playfails', 'item.playfails', 'hint.playfails'),
                    self::cat('/admin/video/pms', 'item.pms', 'hint.pms'),
                    self::cat('/admin/video/notifies', 'item.notifies', 'hint.notifies'),
                ],
            ],
            [
                'id' => 'member',
                'title' => 'more.member',
                'hint' => 'more.member_hint',
                'items' => [
                    self::cat('/admin/video/withdraws', 'item.withdraws', 'hint.withdraws'),
                    self::cat('/admin/video/invites', 'item.invites', 'hint.invites'),
                    self::cat('/admin/video/favorites', 'item.favorites', 'hint.favorites'),
                    self::cat('/admin/video/settings?tab=interact', 'item.config_user', 'hint.config_user', 'moved', 'hint.config_user_keys'),
                ],
            ],
            [
                'id' => 'site',
                'title' => 'more.site',
                'hint' => 'more.site_hint',
                'items' => [
                    self::cat('/admin/video/settings', 'nav.settings', 'hint.settings', '', 'hint.settings_keys'),
                    self::cat('/admin/video/rewrite', 'item.rewrite', 'hint.rewrite'),
                    self::cat('/admin/video/domains', 'item.domains', 'hint.domains'),
                    self::cat('/admin/video/push', 'item.push', 'hint.push'),
                    self::cat('/admin/stats', 'item.stats', 'hint.stats'),
                    self::cat('/admin/video/ulogs', 'item.ulogs', 'hint.ulogs'),
                ],
            ],
            [
                'id' => 'security',
                'title' => 'more.security',
                'hint' => 'more.security_hint',
                'items' => [
                    self::cat('/admin/video/config/api', 'item.config_api', 'hint.config_api'),
                    self::cat('/admin/video/apidoc', 'item.apidoc', 'hint.apidoc'),
                    self::cat('/admin/video/config/ip', 'item.config_ip', 'hint.config_ip'),
                    self::cat('/admin/video/safety', 'item.safety', 'hint.safety'),
                    self::cat('/admin/video/accesslogs', 'item.accesslogs', 'hint.accesslogs'),
                    self::cat('/admin/video/botlogs', 'item.botlogs', 'hint.botlogs'),
                ],
            ],
            [
                'id' => 'system',
                'title' => 'more.system',
                'hint' => 'more.system_hint',
                'items' => [
                    self::cat('/admin/system/menus', 'item.menus', 'hint.menus'),
                    self::cat('/admin/system/dicts', 'item.dicts', 'hint.dicts'),
                    self::cat('/admin/system/attachments', 'item.attachments', 'hint.attachments'),
                    self::cat('/admin/video/tools/annex', 'item.annex', 'hint.annex'),
                    self::cat('/admin/system/tools/cache', 'item.cache', 'hint.cache'),
                    self::cat('/admin/system/tools/schedule', 'item.schedule', 'hint.schedule'),
                    self::cat('/admin/system/monitor/operate-logs', 'item.operate_logs', 'hint.operate_logs'),
                    self::cat('/admin/system/monitor/system-logs', 'item.system_logs', 'hint.system_logs'),
                    self::cat('/admin/system/database/backup', 'item.db_backup', 'hint.db_backup'),
                    self::cat('/admin/system/database/restore', 'item.db_restore', 'hint.db_restore'),
                    self::cat('/admin/system/database/dict', 'item.db_dict', 'hint.db_dict'),
                    self::cat('/admin/system/database/sql', 'item.db_sql', 'hint.db_sql'),
                    self::cat('/admin/system/database/replace', 'item.db_replace', 'hint.db_replace'),
                    self::cat('/admin/system/shortcut', 'item.shortcut', 'hint.shortcut'),
                ],
            ],
            [
                'id' => 'legacy',
                'title' => 'more.legacy',
                'hint' => 'more.legacy_hint',
                'fold' => true,
                'items' => [
                    self::cat('/admin/video/roles', 'item.roles', 'hint.roles'),
                    self::cat('/admin/video/plots', 'item.plots', 'hint.plots'),
                    self::cat('/admin/video/websites', 'item.websites', 'hint.websites'),
                    self::cat('/admin/video/classes', 'item.classes', 'hint.classes'),
                    self::cat('/admin/video/synonyms', 'item.synonyms', 'hint.synonyms'),
                    self::cat('/admin/video/wizard', 'item.wizard', 'hint.wizard'),
                    self::cat('/admin/video/downloaders', 'item.downloaders', 'hint.downloaders'),
                    self::cat('/admin/video/servers', 'item.servers', 'hint.servers'),
                    self::cat('/admin/video/tools/quality', 'item.quality', 'hint.quality'),
                ],
            ],
        ];
        $host = self::host();
        foreach ($blocks as &$block) {
            $extra = $host->catalogItems($block['title']);
            foreach ($extra as $item) {
                if (empty($item['hint'])) {
                    $item['hint'] = 'hint.plugin';
                }
                if (empty($item['tag'])) {
                    $item['tag'] = 'plugin';
                }
                $block['items'][] = $item;
            }
        }
        unset($block);

        return $blocks;
    }

    /** @return array{url:string,label:string,hint:string,tag?:string,keywords?:string} */
    private static function cat(string $url, string $label, string $hint, string $tag = '', string $keywords = ''): array
    {
        $row = [
            'url' => $url,
            'label' => $label,
            'hint' => $hint,
        ];
        if ($tag !== '') {
            $row['tag'] = $tag;
        }
        if ($keywords !== '') {
            $row['keywords'] = $keywords;
        }

        return $row;
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

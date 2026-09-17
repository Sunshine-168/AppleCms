<?php

namespace App\Support;

use App\Support\Plugins\PluginHost;

class AdminNav
{
    /**
     * 顶栏工作区。不是多产品控制台：没有漫画、直播、入金。插件单独一栏。
     *
     * @return list<array{id:string,label:string,home:string}>
     */
    public static function modules(): array
    {
        return [
            ['id' => 'work', 'label' => 'nav.overview', 'home' => '/admin/welcome'],
            ['id' => 'vod', 'label' => 'nav.videos', 'home' => '/admin/video'],
            ['id' => 'collect', 'label' => 'nav.collects', 'home' => '/admin/video/collects'],
            ['id' => 'member', 'label' => 'nav.members', 'home' => '/admin/video/members'],
            ['id' => 'site', 'label' => 'nav.site', 'home' => '/admin/video/settings'],
            ['id' => 'system', 'label' => 'nav.system', 'home' => '/admin/user'],
            ['id' => 'plugin', 'label' => 'nav.plugins', 'home' => '/admin/plugins'],
        ];
    }

    public static function currentModule(?string $path = null): string
    {
        $path = self::normPath($path);
        $best = '';
        $module = 'work';
        foreach (self::modulePrefixes() as $prefix => $id) {
            $hit = $path === $prefix || str_starts_with($path, $prefix.'/');
            if ($hit && strlen($prefix) >= strlen($best)) {
                $best = $prefix;
                $module = $id;
            }
        }
        $pluginBest = '';
        foreach (self::pluginPrefixes() as $prefix) {
            $hit = $path === $prefix || str_starts_with($path, $prefix.'/');
            if ($hit && strlen($prefix) >= strlen($pluginBest)) {
                $pluginBest = $prefix;
            }
        }
        if ($pluginBest !== '' && strlen($pluginBest) >= strlen($best)) {
            return 'plugin';
        }

        return $module;
    }

    /**
     * 当前工作区的侧栏。
     *
     * @return list<array<string, mixed>>
     */
    public static function groups(): array
    {
        return self::groupsFor(self::currentModule());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function groupsFor(string $module): array
    {
        $all = self::moduleGroups();
        $groups = $all[$module] ?? $all['work'];
        $host = self::host();
        if ($module === 'plugin') {
            $groups[0]['items'] = array_merge($groups[0]['items'] ?? [], $host->allSidebarFoldItems());

            return $groups;
        }
        $pluginGroup = [
            'vod' => 'content',
            'member' => 'users',
            'site' => 'site',
            'system' => 'system',
        ][$module] ?? '';
        foreach ($groups as &$group) {
            if ($pluginGroup === '' || empty($group['fold'])) {
                continue;
            }
            $group['fold']['items'] = array_merge($group['fold']['items'], $host->sidebarFoldItems($pluginGroup));
        }
        unset($group);

        return $groups;
    }

    /**
     * 角色能勾的页：侧栏 + 全部功能，按工作区分组。
     * 工作台 / 全部功能 / 插件人人都能进，不进这张表。
     *
     * @return list<array{id:string,label:string,items:list<array{url:string,label:string,icon:string}>}>
     */
    public static function workspacePages(): array
    {
        $byId = [];
        foreach (self::modules() as $mod) {
            $id = (string) ($mod['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $byId[$id] = [
                'id' => $id,
                'label' => admin_t((string) ($mod['label'] ?? $id)),
                'items' => [],
            ];
        }

        $seen = [];
        $add = static function (string $module, string $url, string $labelKey, string $icon) use (&$byId, &$seen): void {
            $url = explode('?', explode('#', $url)[0])[0];
            $url = '/' . ltrim($url, '/');
            if ($url === '/' || isset($seen[$url]) || ! isset($byId[$module])) {
                return;
            }
            if (in_array($url, ['/admin/welcome', '/admin/more', '/admin/plugins'], true)) {
                return;
            }
            $seen[$url] = true;
            $label = self::pageLabel($labelKey);
            if ($label === '') {
                $label = $url;
            }
            $icon = trim(preg_replace('/^fas\s+fa-/', '', $icon) ?? $icon);
            $byId[$module]['items'][] = [
                'url' => $url,
                'label' => $label,
                'icon' => $icon,
            ];
        };

        foreach (array_keys($byId) as $id) {
            foreach (self::groupsFor($id) as $group) {
                $rows = array_merge($group['items'] ?? [], $group['fold']['items'] ?? []);
                foreach ($rows as $item) {
                    if (! empty($item['force'])) {
                        continue;
                    }
                    $add(
                        $id,
                        (string) ($item['url'] ?? ''),
                        (string) ($item['label'] ?? ''),
                        (string) ($item['icon'] ?? '')
                    );
                }
            }
        }

        foreach (self::catalog() as $block) {
            foreach ($block['items'] ?? [] as $item) {
                $url = (string) ($item['url'] ?? '');
                if ($url === '') {
                    continue;
                }
                $add(
                    self::currentModule($url),
                    $url,
                    (string) ($item['label'] ?? ''),
                    (string) ($item['icon'] ?? '')
                );
            }
        }

        $out = [];
        foreach ($byId as $mod) {
            if ($mod['items'] !== []) {
                $out[] = $mod;
            }
        }

        return $out;
    }

    public static function activeUrl(?string $path = null): string
    {
        $path = self::normPath($path);
        if (in_array($path, [
            '/admin/system/monitor/operate-logs',
            '/admin/system/monitor/system-logs',
        ], true) || str_starts_with($path, '/admin/system/monitor/operate-logs/')
            || str_starts_with($path, '/admin/system/monitor/system-logs/')) {
            $path = '/admin/system/monitor/login-logs';
        }
        if (in_array($path, [
            '/admin/system/database/restore',
            '/admin/system/database/sql',
            '/admin/system/database/replace',
            '/admin/system/database/dict',
        ], true) || str_starts_with($path, '/admin/system/database/restore/')
            || str_starts_with($path, '/admin/system/database/sql/')
            || str_starts_with($path, '/admin/system/database/replace/')
            || str_starts_with($path, '/admin/system/database/dict/')) {
            $path = '/admin/system/database/backup';
        }
        $best = '';
        foreach (array_keys(self::moduleGroups()) as $module) {
            foreach (self::groupsFor($module) as $group) {
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
        }

        return $best;
    }

    public static function foldOpen(array $group, string $activeUrl): bool
    {
        foreach ($group['fold']['items'] ?? [] as $item) {
            $href = explode('?', explode('#', (string) ($item['url'] ?? ''))[0])[0];
            if ($href !== '' && ($href === $activeUrl || str_starts_with($activeUrl, $href.'/'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * 低频入口：搜得到即可，不进侧栏。
     *
     * @return list<array<string, mixed>>
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
                    self::cat('/admin/video/tools/quality', 'item.quality', 'hint.quality'),
                    self::cat('/admin/video/roles', 'item.roles', 'hint.roles'),
                    self::cat('/admin/video/plots', 'item.plots', 'hint.plots'),
                    self::cat('/admin/video/classes', 'item.classes', 'hint.classes'),
                    self::cat('/admin/video/synonyms', 'item.synonyms', 'hint.synonyms'),
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
                    self::cat('/admin/video/websites', 'item.websites', 'hint.websites'),
                    self::cat('/admin/video/wizard', 'item.wizard', 'hint.wizard'),
                    self::cat('/admin/video/downloaders', 'item.downloaders', 'hint.downloaders'),
                    self::cat('/admin/video/servers', 'item.servers', 'hint.servers'),
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
                    self::cat('/admin/system/monitor/login-logs', 'nav.logs', 'hint.logs'),
                    self::cat('/admin/system/database/backup', 'nav.database', 'hint.database', '', 'hint.database_keys'),
                    self::cat('/admin/system/shortcut', 'item.shortcut', 'hint.shortcut'),
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

    private static function pageLabel(string $key): string
    {
        $key = trim($key);
        if ($key === '') {
            return '';
        }
        if (preg_match('/^(nav|item|page|hint|more)\./', $key) === 1) {
            $line = admin_t($key);

            return $line !== '' && $line !== $key && $line !== 'admin.'.$key ? $line : $key;
        }

        return $key;
    }

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

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private static function moduleGroups(): array
    {
        return [
            'work' => [[
                'items' => [
                    ['url' => '/admin/welcome', 'icon' => 'tachometer-alt', 'label' => 'nav.dashboard', 'force' => true],
                    ['url' => '/admin/stats', 'icon' => 'chart-line', 'label' => 'nav.stats'],
                    ['url' => '/admin/system/shortcut', 'icon' => 'thumbtack', 'label' => 'nav.shortcut'],
                    ['url' => '/admin/plugins', 'icon' => 'puzzle-piece', 'label' => 'nav.plugins', 'force' => true],
                    ['url' => '/admin/more', 'icon' => 'search', 'label' => 'nav.find', 'force' => true],
                ],
            ]],
            'plugin' => [[
                'items' => [
                    ['url' => '/admin/plugins', 'icon' => 'puzzle-piece', 'label' => 'item.plugins', 'force' => true],
                ],
            ]],
            'vod' => [[
                'items' => [
                    ['url' => '/admin/video', 'icon' => 'video', 'label' => 'nav.videos'],
                    ['url' => '/admin/video/types', 'icon' => 'sitemap', 'label' => 'nav.types'],
                    ['url' => '/admin/video/comments', 'icon' => 'comments', 'label' => 'nav.comments'],
                    ['url' => '/admin/video/topics', 'icon' => 'layer-group', 'label' => 'nav.topics'],
                    ['url' => '/admin/video/actors', 'icon' => 'user-friends', 'label' => 'nav.actors'],
                ],
                'fold' => [
                    'label' => 'nav.more',
                    'items' => [
                        ['url' => '/admin/video/tags', 'icon' => 'tags', 'label' => 'nav.tags'],
                        ['url' => '/admin/video/arts', 'icon' => 'file-alt', 'label' => 'nav.arts'],
                        ['url' => '/admin/video/slides', 'icon' => 'images', 'label' => 'nav.slides'],
                        ['url' => '/admin/video/tools/recycle', 'icon' => 'trash-alt', 'label' => 'nav.recycle'],
                        ['url' => '/admin/video/roles', 'icon' => 'theater-masks', 'label' => 'item.roles'],
                        ['url' => '/admin/video/plots', 'icon' => 'book-open', 'label' => 'item.plots'],
                        ['url' => '/admin/video/classes', 'icon' => 'stream', 'label' => 'item.classes'],
                        ['url' => '/admin/video/synonyms', 'icon' => 'exchange-alt', 'label' => 'item.synonyms'],
                        ['url' => '/admin/video/tools/quality', 'icon' => 'clipboard-check', 'label' => 'item.quality'],
                        ['url' => '/admin/video/tools/images', 'icon' => 'image', 'label' => 'item.images'],
                    ],
                ],
            ]],
            'collect' => [[
                'items' => [
                    ['url' => '/admin/video/collects', 'icon' => 'cloud-download-alt', 'label' => 'nav.collects'],
                    ['url' => '/admin/video/collect_tasks', 'icon' => 'clock', 'label' => 'item.collect_tasks'],
                    ['url' => '/admin/video/collect_logs', 'icon' => 'list-alt', 'label' => 'item.collect_logs'],
                    ['url' => '/admin/video/collect_temps', 'icon' => 'inbox', 'label' => 'item.collect_temps'],
                    ['url' => '/admin/video/audits', 'icon' => 'filter', 'label' => 'item.audits'],
                ],
                'fold' => [
                    'label' => 'nav.more',
                    'items' => [
                        ['url' => '/admin/video/tools/hub', 'icon' => 'plug', 'label' => 'item.collect_hub'],
                        ['url' => '/admin/video/unions', 'icon' => 'share-alt', 'label' => 'item.unions'],
                        ['url' => '/admin/video/config/collect', 'icon' => 'sliders-h', 'label' => 'item.config_collect'],
                        ['url' => '/admin/video/config/interface', 'icon' => 'key', 'label' => 'item.config_interface'],
                    ],
                ],
            ]],
            'member' => [[
                'items' => [
                    ['url' => '/admin/video/members', 'icon' => 'users', 'label' => 'nav.members'],
                    ['url' => '/admin/video/orders', 'icon' => 'file-invoice', 'label' => 'nav.orders'],
                    ['url' => '/admin/video/groups', 'icon' => 'user-tag', 'label' => 'nav.groups'],
                    ['url' => '/admin/video/cards', 'icon' => 'credit-card', 'label' => 'nav.cards'],
                    ['url' => '/admin/video/plogs', 'icon' => 'coins', 'label' => 'nav.plogs'],
                ],
                'fold' => [
                    'label' => 'nav.more',
                    'items' => [
                        ['url' => '/admin/video/withdraws', 'icon' => 'hand-holding-usd', 'label' => 'item.withdraws'],
                        ['url' => '/admin/video/invites', 'icon' => 'ticket-alt', 'label' => 'item.invites'],
                        ['url' => '/admin/video/favorites', 'icon' => 'star', 'label' => 'item.favorites'],
                        ['url' => '/admin/video/pms', 'icon' => 'envelope', 'label' => 'item.pms'],
                        ['url' => '/admin/video/notifies', 'icon' => 'bell', 'label' => 'item.notifies'],
                    ],
                ],
            ]],
            'site' => [[
                'items' => [
                    ['url' => '/admin/video/settings', 'icon' => 'cog', 'label' => 'nav.settings'],
                    ['url' => '/admin/video/templates', 'icon' => 'palette', 'label' => 'nav.templates'],
                    ['url' => '/admin/video/ads', 'icon' => 'bullhorn', 'label' => 'nav.ads'],
                    ['url' => '/admin/video/players', 'icon' => 'play-circle', 'label' => 'nav.players'],
                    ['url' => '/admin/video/links', 'icon' => 'link', 'label' => 'nav.links'],
                ],
                'fold' => [
                    'label' => 'nav.more',
                    'items' => [
                        ['url' => '/admin/video/make', 'icon' => 'bolt', 'label' => 'nav.make'],
                        ['url' => '/admin/video/websites', 'icon' => 'compass', 'label' => 'item.websites'],
                        ['url' => '/admin/video/downloaders', 'icon' => 'download', 'label' => 'item.downloaders'],
                        ['url' => '/admin/video/servers', 'icon' => 'server', 'label' => 'item.servers'],
                        ['url' => '/admin/video/wizard', 'icon' => 'magic', 'label' => 'item.wizard'],
                        ['url' => '/admin/video/rewrite', 'icon' => 'code', 'label' => 'item.rewrite'],
                        ['url' => '/admin/video/domains', 'icon' => 'globe', 'label' => 'item.domains'],
                        ['url' => '/admin/video/push', 'icon' => 'paper-plane', 'label' => 'item.push'],
                        ['url' => '/admin/video/tools/players', 'icon' => 'random', 'label' => 'item.batch_players'],
                    ],
                ],
            ]],
            'system' => [[
                'items' => [
                    ['url' => '/admin/user', 'icon' => 'user-cog', 'label' => 'nav.admins'],
                    ['url' => '/admin/system/roles', 'icon' => 'user-shield', 'label' => 'nav.roles'],
                    ['url' => '/admin/system/menus', 'icon' => 'sitemap', 'label' => 'nav.menus'],
                    ['url' => '/admin/system/monitor/login-logs', 'icon' => 'history', 'label' => 'nav.logs'],
                    ['url' => '/admin/system/database/backup', 'icon' => 'database', 'label' => 'nav.database'],
                ],
                'fold' => [
                    'label' => 'nav.more',
                    'items' => [
                        ['url' => '/admin/system/dicts', 'icon' => 'book', 'label' => 'item.dicts'],
                        ['url' => '/admin/system/attachments', 'icon' => 'folder-open', 'label' => 'item.attachments'],
                        ['url' => '/admin/system/tools/cache', 'icon' => 'memory', 'label' => 'item.cache'],
                        ['url' => '/admin/system/tools/schedule', 'icon' => 'tasks', 'label' => 'item.schedule'],
                        ['url' => '/admin/video/config/api', 'icon' => 'project-diagram', 'label' => 'item.config_api'],
                        ['url' => '/admin/video/config/ip', 'icon' => 'shield-alt', 'label' => 'item.config_ip'],
                        ['url' => '/admin/video/safety', 'icon' => 'bug', 'label' => 'item.safety'],
                    ],
                ],
            ]],
        ];
    }

    /** @return array<string, string> */
    private static function modulePrefixes(): array
    {
        return [
            '/admin/video/config/interface' => 'collect',
            '/admin/video/config/collect' => 'collect',
            '/admin/video/config/player' => 'site',
            '/admin/video/config/api' => 'system',
            '/admin/video/config/ip' => 'system',
            '/admin/video/config/ai' => 'site',
            '/admin/video/tools/players' => 'site',
            '/admin/video/tools/quality' => 'vod',
            '/admin/video/tools/images' => 'vod',
            '/admin/video/tools/recycle' => 'vod',
            '/admin/video/tools/annex' => 'system',
            '/admin/video/tools/hub' => 'collect',
            '/admin/video/collect_logs' => 'collect',
            '/admin/video/collect_tasks' => 'collect',
            '/admin/video/collect_temps' => 'collect',
            '/admin/video/collects' => 'collect',
            '/admin/video/unions' => 'collect',
            '/admin/video/audits' => 'collect',
            '/admin/video/cj' => 'collect',
            '/admin/video/members' => 'member',
            '/admin/video/orders' => 'member',
            '/admin/video/groups' => 'member',
            '/admin/video/cards' => 'member',
            '/admin/video/plogs' => 'member',
            '/admin/video/withdraws' => 'member',
            '/admin/video/invites' => 'member',
            '/admin/video/favorites' => 'member',
            '/admin/video/pms' => 'member',
            '/admin/video/notifies' => 'member',
            '/admin/video/settings' => 'site',
            '/admin/video/templates' => 'site',
            '/admin/video/ads' => 'site',
            '/admin/video/players' => 'site',
            '/admin/video/links' => 'site',
            '/admin/video/make' => 'site',
            '/admin/video/websites' => 'site',
            '/admin/video/downloaders' => 'site',
            '/admin/video/servers' => 'site',
            '/admin/video/wizard' => 'site',
            '/admin/video/rewrite' => 'site',
            '/admin/video/domains' => 'site',
            '/admin/video/push' => 'site',
            '/admin/video/config' => 'site',
            '/admin/video/safety' => 'system',
            '/admin/video/accesslogs' => 'system',
            '/admin/video/botlogs' => 'system',
            '/admin/video/apidoc' => 'system',
            '/admin/system/shortcut' => 'work',
            '/admin/system' => 'system',
            '/admin/user' => 'system',
            '/admin/plugins' => 'plugin',
            '/admin/set' => 'system',
            '/admin/stats' => 'work',
            '/admin/more' => 'work',
            '/admin/welcome' => 'work',
            '/admin/video' => 'vod',
            '/admin' => 'work',
        ];
    }

    /** @return list<string> */
    private static function pluginPrefixes(): array
    {
        $out = ['/admin/plugins'];
        foreach (self::host()->allSidebarFoldItems() as $item) {
            $href = explode('?', explode('#', (string) ($item['url'] ?? ''))[0])[0];
            $href = rtrim($href, '/');
            if ($href !== '') {
                $out[] = $href[0] === '/' ? $href : '/'.$href;
            }
        }

        return array_values(array_unique($out));
    }

    private static function normPath(?string $path): string
    {
        $path = $path ?? ('/'.ltrim((string) request()->path(), '/'));
        $path = explode('?', explode('#', $path)[0])[0];
        if ($path === '') {
            return '/admin';
        }
        if ($path[0] !== '/') {
            $path = '/'.$path;
        }

        $path = rtrim($path, '/');

        return $path === '' ? '/' : $path;
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

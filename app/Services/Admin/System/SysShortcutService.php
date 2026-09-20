<?php

namespace App\Services\Admin\System;

use App\Services\Admin\VideoDashboardService;
use App\Support\Utils\Result;

class SysShortcutService
{
    /**
     * @return array{groups: list<array<string, mixed>>, todo_total: int}
     */
    public function pageBoard(): array
    {
        $n = $this->counts();
        $todo = $n['comment_pending'] + $n['report_open'] + $n['playfail_open'] + $n['gbook_pending'] + $n['collect_fail'];

        return [
            'todo_total' => $todo,
            'groups' => [
                [
                    'id' => 'todo',
                    'title' => admin_t('ui.sc_todo'),
                    'hint' => $todo > 0 ? admin_t('ui.sc_todo_hint') : admin_t('ui.sc_todo_empty'),
                    'items' => [
                        ['title' => admin_t('ui.sc_comment'), 'desc' => admin_t('ui.sc_comment_desc'), 'url' => '/admin/video/comments', 'count' => $n['comment_pending']],
                        ['title' => admin_t('ui.sc_cant_play'), 'desc' => admin_t('ui.sc_cant_play_desc'), 'url' => '/admin/video/reports', 'count' => $n['report_open']],
                        ['title' => admin_t('ui.sc_bad_link'), 'desc' => admin_t('ui.sc_bad_link_desc'), 'url' => '/admin/video/playfails', 'count' => $n['playfail_open']],
                        ['title' => admin_t('ui.sc_gbook'), 'desc' => admin_t('ui.sc_gbook_desc'), 'url' => '/admin/video/guestbooks', 'count' => $n['gbook_pending']],
                        ['title' => admin_t('ui.sc_collect_fail'), 'desc' => admin_t('ui.sc_collect_fail_desc'), 'url' => '/admin/video/collect_logs?ok=0', 'count' => $n['collect_fail']],
                    ],
                ],
                [
                    'id' => 'plugins',
                    'title' => admin_t('nav.plugins'),
                    'hint' => admin_t('ui.sc_plugins_hint'),
                    'items' => [
                        ['title' => admin_t('nav.plugins'), 'desc' => admin_t('ui.sc_plugins_desc'), 'url' => '/admin/plugins'],
                    ],
                ],
                [
                    'id' => 'library',
                    'title' => admin_t('ui.sc_library'),
                    'hint' => admin_t('ui.sc_library_hint'),
                    'items' => [
                        ['title' => admin_t('nav.videos'), 'desc' => admin_t('ui.sc_videos_desc'), 'url' => '/admin/video'],
                        ['title' => admin_t('nav.collects'), 'desc' => admin_t('ui.sc_collect_desc'), 'url' => '/admin/video/collects'],
                        ['title' => admin_t('nav.types'), 'desc' => admin_t('ui.sc_types_desc'), 'url' => '/admin/video/types'],
                        ['title' => admin_t('nav.members'), 'desc' => admin_t('ui.sc_members_desc'), 'url' => '/admin/video/members'],
                    ],
                ],
                [
                    'id' => 'site',
                    'title' => admin_t('ui.sc_site'),
                    'hint' => admin_t('ui.sc_site_hint'),
                    'items' => [
                        ['title' => admin_t('nav.settings'), 'desc' => admin_t('ui.sc_settings_desc'), 'url' => '/admin/video/settings'],
                        ['title' => admin_t('nav.templates'), 'desc' => admin_t('ui.sc_tpl_desc'), 'url' => '/admin/video/templates'],
                        ['title' => admin_t('nav.ads'), 'desc' => admin_t('ui.sc_ads_desc'), 'url' => '/admin/video/ads'],
                        ['title' => admin_t('nav.make'), 'desc' => admin_t('ui.sc_make_desc'), 'url' => '/admin/video/make'],
                    ],
                ],
                [
                    'id' => 'maintain',
                    'title' => admin_t('ui.sc_maintain'),
                    'hint' => admin_t('ui.sc_maintain_hint'),
                    'items' => [
                        ['title' => admin_t('ui.sc_cache'), 'desc' => admin_t('ui.sc_cache_desc'), 'url' => '/admin/system/tools/cache'],
                        ['title' => admin_t('ui.sc_backup'), 'desc' => admin_t('ui.sc_backup_desc'), 'url' => '/admin/system/database/backup'],
                        ['title' => admin_t('ui.sc_ip'), 'desc' => admin_t('ui.sc_ip_desc'), 'url' => '/admin/video/config/ip'],
                        ['title' => admin_t('nav.logs'), 'desc' => admin_t('ui.sc_logs_desc'), 'url' => '/admin/system/monitor/login-logs'],
                        ['title' => admin_t('nav.admins'), 'desc' => admin_t('ui.sc_admins_desc'), 'url' => '/admin/user'],
                    ],
                ],
            ],
        ];
    }

    public function getShortcutList(): array
    {
        $items = [];
        foreach ($this->pageBoard()['groups'] as $group) {
            foreach ($group['items'] as $item) {
                $items[] = [
                    'title' => $item['title'],
                    'desc' => $item['desc'],
                    'url' => $item['url'],
                    'count' => (int) ($item['count'] ?? 0),
                ];
            }
        }

        return Result::success([
            'data' => $items,
            'total' => count($items),
        ]);
    }

    /** @return array<string, int> */
    private function counts(): array
    {
        $zero = [
            'comment_pending' => 0,
            'report_open' => 0,
            'playfail_open' => 0,
            'gbook_pending' => 0,
            'collect_fail' => 0,
        ];
        try {
            return array_merge($zero, app(VideoDashboardService::class)->counts());
        } catch (\Throwable) {
            return $zero;
        }
    }
}

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
                    'title' => '要处理',
                    'hint' => $todo > 0 ? '有数字的先点' : '现在没有待办',
                    'items' => [
                        ['title' => '评论', 'desc' => '片子下待审的留言', 'url' => '/admin/video/comments', 'count' => $n['comment_pending']],
                        ['title' => '无法播放', 'desc' => '详情页报过来的', 'url' => '/admin/video/reports', 'count' => $n['report_open']],
                        ['title' => '坏链', 'desc' => '播放页点报错记下的', 'url' => '/admin/video/playfails', 'count' => $n['playfail_open']],
                        ['title' => '留言', 'desc' => '站点留言板', 'url' => '/admin/video/guestbooks', 'count' => $n['gbook_pending']],
                        ['title' => '采集失败', 'desc' => '今天没采成功的', 'url' => '/admin/video/collect_logs?ok=0', 'count' => $n['collect_fail']],
                    ],
                ],
                [
                    'id' => 'library',
                    'title' => '片库',
                    'hint' => '片子怎么进来、怎么分类',
                    'items' => [
                        ['title' => '影片', 'desc' => '片库列表，点进去改', 'url' => '/admin/video'],
                        ['title' => '采集', 'desc' => '从资源站拉片子', 'url' => '/admin/video/collects'],
                        ['title' => '分类', 'desc' => '前台栏目', 'url' => '/admin/video/types'],
                        ['title' => '会员', 'desc' => '前台账号，不是管理员', 'url' => '/admin/video/members'],
                    ],
                ],
                [
                    'id' => 'site',
                    'title' => '站点',
                    'hint' => '名字、外观、生成页',
                    'items' => [
                        ['title' => '站点设置', 'desc' => '站名、评论、SEO', 'url' => '/admin/video/settings'],
                        ['title' => '模板', 'desc' => '前台长什么样', 'url' => '/admin/video/templates'],
                        ['title' => '广告', 'desc' => '投放位', 'url' => '/admin/video/ads'],
                        ['title' => '静态生成', 'desc' => '全页缓存和磁盘页', 'url' => '/admin/video/make'],
                    ],
                ],
                [
                    'id' => 'maintain',
                    'title' => '维护',
                    'hint' => '改完不生效、备份、谁改过',
                    'items' => [
                        ['title' => '缓存', 'desc' => '改完还不生效时清', 'url' => '/admin/system/tools/cache'],
                        ['title' => '备份', 'desc' => '导出数据库', 'url' => '/admin/system/database/backup'],
                        ['title' => 'IP 白名单', 'desc' => '只拦后台登录', 'url' => '/admin/video/config/ip'],
                        ['title' => '操作日志', 'desc' => '谁改过什么', 'url' => '/admin/system/monitor/operate-logs'],
                        ['title' => '管理员', 'desc' => '能进后台的人', 'url' => '/admin/user'],
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

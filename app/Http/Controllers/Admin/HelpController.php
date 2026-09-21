<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpController extends Controller
{
    /** @var list<string> */
    private const TOPICS = [
        'use', 'admin', 'templates', 'tags', 'env', 'schedule', 'laravel', 'docker',
    ];

    public function index(Request $request): View
    {
        $topic = (string) $request->query('topic', 'use');
        if (! in_array($topic, self::TOPICS, true)) {
            $topic = 'use';
        }

        return view('admin.help.index', [
            'topic' => $topic,
            'urls' => $this->urls(),
        ]);
    }

    /** @return array<string, string> */
    private function urls(): array
    {
        return [
            'dashboard' => '/admin/welcome',
            'stats' => '/admin/stats',
            'settings' => '/admin/video/settings',
            'types' => '/admin/video/types',
            'videos' => '/admin/video',
            'collects' => '/admin/video/collects',
            'players' => '/admin/video/players',
            'make' => '/admin/video/make',
            'templates' => '/admin/video/templates',
            'wizard' => '/admin/video/wizard',
            'plugins' => '/admin/plugins',
            'ai' => '/admin/video/config/ai',
            'members' => '/admin/video/members',
            'comments' => '/admin/video/comments',
            'gbook' => '/admin/video/gbook',
            'arts' => '/admin/video/arts',
            'topics' => '/admin/video/topics',
            'actors' => '/admin/video/actors',
            'ads' => '/admin/video/ads',
            'links' => '/admin/video/links',
            'cache' => '/admin/system/tools/cache',
            'schedule' => '/admin/system/tools/schedule',
            'admins' => '/admin/user',
            'roles' => '/admin/system/roles',
            'menus' => '/admin/system/menus',
            'logs' => '/admin/system/monitor/login-logs',
            'rewrite' => '/admin/video/rewrite',
            'push' => '/admin/video/push',
            'domains' => '/admin/video/domains',
            'database' => '/admin/system/database/backup',
            'front' => url('/'),
            'install' => url('/install'),
            'helpAdmin' => route('admin.help', ['topic' => 'admin']),
            'helpTemplates' => route('admin.help', ['topic' => 'templates']),
            'tags' => route('admin.help', ['topic' => 'tags']),
            'env' => route('admin.help', ['topic' => 'env']),
            'scheduleHelp' => route('admin.help', ['topic' => 'schedule']),
        ];
    }
}

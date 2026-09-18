<?php

namespace Tests\Unit;

use App\Support\Plugins\PluginHost;
use Tests\TestCase;

class PluginHostSidebarTest extends TestCase
{
    public function test_sidebar_fold_keeps_children_nested(): void
    {
        $host = new PluginHost();
        $host->sidebarFold('content', [
            'url' => '/admin/video/mangas',
            'icon' => 'book-open',
            'label' => 'nav.manga',
            'children' => [
                ['url' => '/admin/video/mangas', 'label' => 'nav.manga_list'],
                ['url' => '/admin/video/mangas?desk=chapters', 'label' => 'nav.manga_chapters'],
            ],
        ]);
        $host->sidebarFold('content', [
            ['url' => '/admin/video/manga_chapters', 'label' => 'nav.manga_chapters'],
        ]);

        $items = $host->sidebarFoldItems('content');
        $this->assertCount(1, $items);
        $this->assertSame('/admin/video/mangas', $items[0]['url'] ?? '');
        $this->assertSame('nav.manga', $items[0]['label'] ?? '');
        $this->assertCount(2, $items[0]['children'] ?? []);
        $this->assertSame('nav.manga_chapters', $items[0]['children'][1]['label'] ?? '');
        $this->assertSame('/admin/video/mangas?desk=chapters', $items[0]['children'][1]['url'] ?? '');
    }
}

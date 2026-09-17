<?php

namespace Tests\Feature;

use Tests\TestCase;

class TopicIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_topic_index_is_a_playlist_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/topics')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有专题', $html);
        $this->assertStringContainsString('片单', $html);
        $this->assertStringContainsString('js-bind', $html);
        $this->assertStringContainsString('topic-batch', $html);
        $this->assertStringContainsString('新增专题', $html);
        $this->assertStringContainsString('搜专题名', $html);
        $this->assertStringContainsString('js-bind-art', $html);
        $this->assertStringContainsString('绑文', $html);
        $this->assertStringContainsString('副标', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('U.prompt', $html);
    }
}

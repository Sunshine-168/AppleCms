<?php

namespace Tests\Feature;

use Tests\TestCase;

class PlayerIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_player_index_is_an_engine_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/players')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有播放器', $html);
        $this->assertStringContainsString('补齐内置', $html);
        $this->assertStringContainsString('ArtPlayer', $html);
        $this->assertStringContainsString('DPlayer', $html);
        $this->assertStringContainsString('Video.js', $html);
        $this->assertStringContainsString('player-batch', $html);
        $this->assertStringContainsString('/admin/video/tools/players', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
    }
}

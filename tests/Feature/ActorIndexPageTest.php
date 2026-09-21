<?php

namespace Tests\Feature;

use Tests\TestCase;

class ActorIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_actor_index_is_a_people_library_not_a_log_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/actors')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有演员', $html);
        $this->assertStringContainsString('无头像', $html);
        $this->assertStringContainsString('actor-batch', $html);
        $this->assertStringContainsString('新增演员', $html);
        $this->assertStringContainsString('搜演员名', $html);
        $this->assertStringNotContainsString('actor-refresh-btn', $html);
        $this->assertStringNotContainsString('创建时间', $html);
    }
}

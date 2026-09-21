<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_member_index_is_a_people_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/members')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有会员', $html);
        $this->assertStringContainsString('登录的是网站', $html);
        $this->assertStringContainsString('member-batch', $html);
        $this->assertStringContainsString('新建会员', $html);
        $this->assertStringContainsString('搜索昵称、邮箱或 ID', $html);
        $this->assertStringContainsString('已停用', $html);
        $this->assertStringContainsString('调整积分', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
        $this->assertStringNotContainsString('会员组ID', $html);
    }

    public function test_english_ui_translates_seeded_member_groups(): void
    {
        $html = $this->withSession([
            'admin_uid' => 1,
            'admin_username' => 'admin',
            'admin_ui_locale' => 'en',
        ])->get('/admin/video/members')->assertOk()->getContent();

        $this->assertStringContainsString('>Member<', $html);
        $this->assertStringContainsString('>VIP<', $html);
        $this->assertStringNotContainsString('普通会员', $html);
        $this->assertStringContainsString('No members yet', $html);
    }
}


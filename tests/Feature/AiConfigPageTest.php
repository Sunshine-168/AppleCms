<?php

namespace Tests\Feature;

use Tests\TestCase;

class AiConfigPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_ai_config_is_a_key_vault_workbench_not_a_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/config/ai')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('AI 写内容', $html);
        $this->assertStringContainsString('还没有接通模型', $html);
        $this->assertStringContainsString('不会改影片简介', $html);
        $this->assertStringContainsString('填服务商和密钥', $html);
        $this->assertStringContainsString('简介去影片里手写', $html);
        $this->assertStringContainsString('还不会写简介', $html);
        $this->assertStringContainsString('去无简介列表', $html);
        $this->assertStringContainsString('/admin/video?empty_content=1', $html);
        $this->assertStringContainsString('/admin/plugins', $html);
        $this->assertStringContainsString('site-save', $html);
        $this->assertStringContainsString('ai-config-index', $html);
        $this->assertStringContainsString('OpenAI', $html);
        $this->assertStringContainsString('通义', $html);
        $this->assertStringContainsString('文心', $html);
        $this->assertStringNotContainsString('placeholder="openai / 通义 / 文心"', $html);
        $this->assertStringNotContainsString('class="hint"', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
    }
}

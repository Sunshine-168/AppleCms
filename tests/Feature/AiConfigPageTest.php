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

    public function test_ai_config_is_a_generate_workbench_not_a_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/config/ai')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('AI SEO', $html);
        $this->assertStringContainsString('请求模型写简介和 SEO', $html);
        $this->assertStringContainsString('填写密钥', $html);
        $this->assertStringContainsString('试写或到影片里生成', $html);
        $this->assertStringContainsString('试写一段', $html);
        $this->assertStringContainsString('试写 SEO', $html);
        $this->assertStringContainsString('试写不会改影片', $html);
        $this->assertStringContainsString('去无简介列表', $html);
        $this->assertStringContainsString('去无 SEO 列表', $html);
        $this->assertStringContainsString('/admin/video?empty_content=1', $html);
        $this->assertStringContainsString('/admin/video?empty_seo=1', $html);
        $this->assertStringContainsString('/admin/plugins', $html);
        $this->assertStringContainsString('site-save', $html);
        $this->assertStringContainsString('ai-config-index', $html);
        $this->assertStringContainsString('OpenAI', $html);
        $this->assertStringContainsString('DeepSeek', $html);
        $this->assertStringContainsString('https://api.deepseek.com/v1/chat/completions', $html);
        $this->assertStringContainsString('通义', $html);
        $this->assertStringContainsString('文心', $html);
        $this->assertStringNotContainsString('placeholder="openai / 通义 / 文心"', $html);
        $this->assertStringNotContainsString('还没有接通模型', $html);
        $this->assertStringNotContainsString('还不会写简介', $html);
        $this->assertStringNotContainsString('class="hint"', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
    }

    public function test_plugin_card_opens_the_same_ai_workbench(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/plugins/ai_content')
            ->assertRedirect('/admin/video/config/ai');

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->followingRedirects()
            ->get('/admin/plugins/ai_content')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('ai-config-index', $html);
        $this->assertStringContainsString('DeepSeek', $html);
        $this->assertStringContainsString('试写一段', $html);
        $this->assertStringNotContainsString('js-plugin-form', $html);
        $this->assertStringNotContainsString(admin_t('ai.cfg_endpoint_ph'), $html);
    }
}

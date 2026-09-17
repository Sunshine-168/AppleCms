<?php

namespace Tests\Feature;

use App\Support\AdminUi;
use Tests\TestCase;

class AdminUiLocaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_admin_ui_has_the_same_nine_languages_as_maccms(): void
    {
        $codes = AdminUi::codes();
        $this->assertSame(['de', 'en', 'es', 'fr', 'ja', 'ko', 'pt', 'zh_cn', 'zh_tw'], $codes);
        $this->assertSame('zh_cn', AdminUi::normalize('zh-cn'));
        $this->assertSame('en', AdminUi::normalize('en-us'));
        $this->assertSame('ja', AdminUi::normalize('ja-jp'));
        $this->assertSame('zh_tw', AdminUi::normalize('zh-tw'));
        $this->assertSame('', AdminUi::normalize('xx'));
    }

    public function test_topbar_language_dropdown_lists_all_nine(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/welcome')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('lang-pick', $html);
        $this->assertStringContainsString('Deutsch', $html);
        $this->assertStringContainsString('English', $html);
        $this->assertStringContainsString('Español', $html);
        $this->assertStringContainsString('Français', $html);
        $this->assertStringContainsString('日本語', $html);
        $this->assertStringContainsString('한국어', $html);
        $this->assertStringContainsString('Português', $html);
        $this->assertStringContainsString('简体中文', $html);
        $this->assertStringContainsString('繁體中文', $html);
        $this->assertStringContainsString('>DE<', $html);
        $this->assertStringContainsString('>US<', $html);
        $this->assertStringContainsString('清理缓存', $html);
        $this->assertStringContainsString('锁屏操作', $html);
        $this->assertStringContainsString('id="lockOverlay"', $html);
        $this->assertStringContainsString('id="unlockPassword"', $html);
        $this->assertStringContainsString("fetch('/admin/unlock'", $html);
        $this->assertStringContainsString('id="unlockLogout"', $html);
        $this->assertStringContainsString('class="topbar-icon topbar-front"', $html);
        $this->assertStringContainsString('fa-external-link-alt', $html);
        $this->assertStringContainsString('account-menu', $html);
        $this->assertStringContainsString('fa-ellipsis-h', $html);
        $this->assertStringContainsString('id="quickCacheClear"', $html);
        $this->assertStringContainsString('id="lockScreen"', $html);
        $this->assertStringContainsString('id="logoutForm"', $html);
        $this->assertStringContainsString('side-foot', $html);
        $this->assertStringNotContainsString('user-chip', $html);
        $this->assertStringNotContainsString('account-menu-who', $html);
        $this->assertStringNotContainsString('class="ui-switch"', $html);
        $this->assertStringNotContainsString('tool-menu', $html);
        $this->assertStringNotContainsString('quickLogout', $html);
    }

    public function test_switching_to_german_translates_workspace_chrome(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->from('/admin/welcome')
            ->post('/admin/ui-locale', ['ui_locale' => 'de'])
            ->assertRedirect();

        $html = $this->withSession([
            'admin_uid' => 1,
            'admin_username' => 'admin',
            'admin_ui_locale' => 'de',
        ])->get('/admin/welcome')->assertOk()->getContent();

        $this->assertStringContainsString('Arbeitsplatz', $html);
        $this->assertStringContainsString('Cache leeren', $html);
        $this->assertStringContainsString('Bildschirm sperren', $html);
        $this->assertStringContainsString('html lang="de"', $html);
    }

    public function test_login_page_lists_nine_languages(): void
    {
        $html = $this->get('/admin/login?ui=zh_cn')->assertOk()->getContent();
        $this->assertStringContainsString('Deutsch', $html);
        $this->assertStringContainsString('繁體中文', $html);
        $this->assertStringContainsString('日本語', $html);
        $this->assertStringContainsString('ui=de', $html);
    }

    public function test_unlock_rejects_empty_password(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->postJson('/admin/unlock', ['password' => '  '])
            ->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonPath('msg', '请输入密码');
    }
}

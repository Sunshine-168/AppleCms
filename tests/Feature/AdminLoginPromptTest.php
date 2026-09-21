<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminLoginPromptTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_login_page_has_error_box_and_empty_field_prompts(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();

        $this->assertStringContainsString('id="loginError"', $html);
        $this->assertStringContainsString('影片、漫画、小说、图集、直播分栏管理', $html);
        $this->assertStringContainsString('顶栏切换栏目，侧栏只显示当前工作区', $html);
        $this->assertStringContainsString('采集、待审、发布连贯完成', $html);
        $this->assertStringContainsString('后台支持九种语言', $html);
        $this->assertStringNotContainsString('不堆成一长条', $html);
        $this->assertStringNotContainsString('顶栏切 影片', $html);
        $this->assertStringContainsString('data-user="请填写账号"', $html);
        $this->assertStringContainsString('data-pass="请填写密码"', $html);
        $this->assertStringContainsString('data-captcha-empty="请填写验证码"', $html);
        $this->assertStringContainsString('function pickMsg', $html);
        $this->assertStringContainsString('mac-toast', $html);
        $this->assertStringContainsString('/admin/captcha', $html);
        $this->assertStringContainsString('captchaImg', $html);
        $this->assertStringNotContainsString(' = ?', $html);
        $this->assertStringNotContainsString("{{ \$cap['question'] }}", $html);
    }

    public function test_captcha_endpoint_returns_image_and_stores_answer(): void
    {
        $res = $this->get('/admin/captcha');
        $res->assertOk();

        $ctype = (string) $res->headers->get('Content-Type');
        $this->assertTrue(
            str_contains($ctype, 'image/png') || str_contains($ctype, 'image/svg+xml'),
            $ctype
        );
        $this->assertStringContainsString('no-store', (string) $res->headers->get('Cache-Control'));
        $this->assertFalse(str_starts_with(ltrim($res->getContent()), '{'));

        $answer = session('captcha');
        $this->assertNotNull($answer);
        $this->assertTrue(\App\Support\Captcha::check((string) $answer));
    }

    public function test_login_api_returns_captcha_prompt(): void
    {
        $this->postJson('/api/admin/login', [
            'username' => 'admin',
            'password' => 'Admin@12345',
        ])->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonPath('msg', '请输入验证码');
    }

    public function test_login_api_returns_wrong_captcha_prompt(): void
    {
        $this->withSession(['captcha' => 8])
            ->postJson('/api/admin/login', [
                'username' => 'admin',
                'password' => 'Admin@12345',
                'captcha' => '3',
            ])->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonPath('msg', '验证码错误');
    }

    public function test_login_api_returns_account_required_prompt(): void
    {
        $this->postJson('/api/admin/login', [
            'username' => '  ',
            'password' => '',
            'captcha' => '1',
        ])->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonPath('msg', '请填写账号和密码');
    }
}

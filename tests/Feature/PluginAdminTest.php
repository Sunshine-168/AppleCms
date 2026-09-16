<?php

namespace Tests\Feature;

use App\Plugins\PluginManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PluginAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_lists_local_plugins_and_hides_disabled_pages(): void
    {
        $manager = app(PluginManager::class);
        $ids = array_column($manager->listForAdmin(), 'id');
        $this->assertContains('danmaku', $ids);
        $this->assertContains('sms', $ids);
        $this->assertContains('pay', $ids);
        $this->assertContains('coupon', $ids);
        $this->assertFalse($manager->isEnabled('sms'));

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/plugins')
            ->assertOk()
            ->assertSee('弹幕')
            ->assertSee('短信网关')
            ->assertSee('只存配置')
            ->assertSee('/admin/plugins/sms', false);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/plugins/sms')
            ->assertOk()
            ->assertSee('AccessKey')
            ->assertSee('保存参数');

        $hostPages = array_keys(app(\App\Plugins\PluginHost::class)->extraPages());
        $extraPages = array_keys(app(\App\Services\Video\VideoSettingService::class)->extraPages());
        $this->assertNotContains('sms', $hostPages, json_encode($hostPages));
        $this->assertNotContains('sms', $extraPages, json_encode($extraPages));

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/config/sms')
            ->assertNotFound();

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/coupons')
            ->assertNotFound();

        $danmaku = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/danmaku');
        if ($manager->isEnabled('danmaku')) {
            $danmaku->assertOk();
        } else {
            $danmaku->assertNotFound();
        }
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class CollectIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_collect_index_uses_workflow_not_a_log_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/collects')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有采集源', $html);
        $this->assertStringContainsString('js-today', $html);
        $this->assertStringContainsString('未绑定', $html);
        $this->assertStringNotContainsString('collect-source-refresh-btn', $html);
        $this->assertStringNotContainsString('起始页,采集页数,小时', $html);
    }
}

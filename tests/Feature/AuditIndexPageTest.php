<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuditIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_audit_index_is_a_rule_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/audits')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有审核规则', $html);
        $this->assertStringContainsString('/admin/video/audits/create', $html);
        $this->assertStringContainsString('搜名称或关键词', $html);
        $this->assertStringContainsString('audit-batch', $html);
        $this->assertStringContainsString('跳过', $html);
        $this->assertStringContainsString('入库并下架', $html);
        $this->assertStringContainsString('/admin/video/collects', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
        $this->assertStringNotContainsString('title: \'scope\'', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
        $this->assertStringNotContainsString('iframe', $html);
    }

    public function test_audit_create_explains_actions_not_raw_schema(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/audits/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('请填写关键词', $html);
        $this->assertStringContainsString('看哪里', $html);
        $this->assertStringContainsString('跳过不入库', $html);
        $this->assertStringContainsString('入库并下架', $html);
        $this->assertStringContainsString('抠词后再入库', $html);
        $this->assertStringContainsString('试一下', $html);
        $this->assertStringContainsString('/admin/video/audits/try', $html);
        $this->assertStringNotContainsString('关键词/正则(逗号或换行)', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
        $this->assertStringNotContainsString('iframe', $html);
        $this->assertStringNotContainsString('mod-add', $html);
    }
}

<?php

namespace Tests\Feature;

use App\Services\Admin\System\SysDictService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DictIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_dict_index_is_a_grouped_option_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/dicts')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('字典', $html);
        $this->assertStringContainsString('还没有字典', $html);
        $this->assertStringContainsString('搜显示名、标识或分组', $html);
        $this->assertStringContainsString('新增选项', $html);
        $this->assertStringContainsString('影片分类不在这里', $html);
        $this->assertStringContainsString('/admin/video/types', $html);
        $this->assertStringContainsString('/admin/system/database/dict', $html);
        $this->assertStringContainsString('已停用', $html);
        $this->assertStringNotContainsString('dict-refresh-btn', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('新增字段', $html);
        $this->assertStringNotContainsString('字段管理', $html);
        $this->assertStringNotContainsString('placeholder="类型"', $html);
        $this->assertStringNotContainsString('placeholder="KEY"', $html);
        $this->assertStringNotContainsString("title: 'KEY'", $html);
        $this->assertStringNotContainsString("title: '值类型'", $html);
        $this->assertStringNotContainsString("title: 'ID'", $html);
    }

    public function test_list_groups_search_and_rejects_empty_key(): void
    {
        $svc = app(SysDictService::class);

        $this->assertSame(1, $svc->addSysSet('', 'paid', 0, '1', null, '已支付', 0, 0, '')['code']);
        $this->assertStringContainsString('分组和标识', $svc->addSysSet('', 'paid', 0, '1', null, '已支付', 0, 0, '')['msg']);

        $this->assertSame(0, $svc->addSysSet('pay_status', 'paid', 0, '已付', null, '已支付', 10, 0, '')['code']);
        $this->assertSame(0, $svc->addSysSet('pay_status', 'wait', 0, '待付', null, '待支付', 9, 0, '')['code']);
        $this->assertSame(0, $svc->addSysSet('member_flag', 'vip', 0, '1', null, 'VIP', 0, 1, '')['code']);
        $dup = $svc->addSysSet('pay_status', 'paid', 0, 'x', null, '重复', 0, 0, '');
        $this->assertSame(1, $dup['code']);
        $this->assertStringContainsString('标识已存在', $dup['msg']);

        $pay = $svc->getSysLists('pay_status', '', '', 20);
        $this->assertSame(0, $pay['code']);
        $this->assertCount(2, $pay['data']['data'] ?? []);
        $this->assertSame('已支付', $pay['data']['data'][0]['title'] ?? '');
        $this->assertTrue($pay['data']['data'][0]['is_on'] ?? false);

        $search = $svc->getSysLists('', '支付', '', 20);
        $this->assertCount(2, $search['data']['data'] ?? []);

        $off = $svc->getSysLists('', '', '1', 20);
        $this->assertCount(1, $off['data']['data'] ?? []);
        $this->assertSame('VIP', $off['data']['data'][0]['title'] ?? '');
        $this->assertFalse($off['data']['data'][0]['is_on'] ?? true);

        $board = $svc->pageBoard();
        $types = array_column($board['types'] ?? [], 'dict_type');
        $this->assertContains('pay_status', $types);
        $this->assertContains('member_flag', $types);
        $this->assertSame(3, $board['queues']['all'] ?? 0);
        $this->assertSame(1, $board['queues']['off'] ?? 0);
    }
}

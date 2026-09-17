<?php

namespace Tests\Feature;

use App\Services\Admin\System\SysDictService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DictIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_dict_index_is_a_simple_option_list(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/dicts')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('dict-index', $html);
        $this->assertStringContainsString('地区', $html);
        $this->assertStringContainsString('语言', $html);
        $this->assertStringContainsString('年份', $html);
        $this->assertStringContainsString('字典值配置', $html);
        $this->assertStringContainsString('字符串', $html);
        $this->assertStringContainsString('JSON对象', $html);
        $this->assertStringContainsString('JSON数组', $html);
        $this->assertStringContainsString('枚举', $html);
        $this->assertStringContainsString('富文本', $html);
        $this->assertStringContainsString('新增字典', $html);
        $this->assertStringContainsString('值类型', $html);
        $this->assertStringContainsString('filter_area', $html);
        $this->assertStringContainsString('已停用', $html);
        $this->assertStringContainsString('按类型收一组选项', $html);
        $this->assertStringNotContainsString('不在这张表', $html);
        $this->assertStringNotContainsString('影片分类', $html);
        $this->assertStringNotContainsString('播放器', $html);
        $this->assertStringNotContainsString('从站点设置拆进来', $html);
        $this->assertStringNotContainsString('hub-steps', $html);
        $this->assertStringNotContainsString('dict-note', $html);
        $this->assertStringNotContainsString('/admin/video/types', $html);
        $this->assertStringNotContainsString('/admin/system/database/dict', $html);
        $this->assertStringNotContainsString('settings?tab=more', $html);
        $this->assertStringNotContainsString('USDT', $html);
        $this->assertStringNotContainsString('余额宝', $html);
        $this->assertStringNotContainsString('dict-refresh-btn', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('新增字段', $html);
        $this->assertStringNotContainsString('字段管理', $html);
        $this->assertStringNotContainsString('placeholder="KEY"', $html);
        $this->assertStringNotContainsString("title: 'KEY'", $html);
    }

    public function test_list_groups_search_and_rejects_empty_key(): void
    {
        $svc = app(SysDictService::class);

        $this->assertSame(1, $svc->addSysSet('', 'paid', 0, '1', null, '已支付', 0, 0, '')['code']);
        $this->assertStringContainsString('分类和标识', $svc->addSysSet('', 'paid', 0, '1', null, '已支付', 0, 0, '')['msg']);

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
        $this->assertContains('filter_area', $types);
        $this->assertContains('pay_status', $types);
        $this->assertContains('member_flag', $types);
        $labels = array_column($board['groups'] ?? [], 'label');
        $this->assertContains('地区', $labels);
        $this->assertNotContains('影片分类', $labels);
        $this->assertSame(3, $board['queues']['all'] ?? 0);
        $this->assertSame(1, $board['queues']['off'] ?? 0);
    }

    public function test_filter_choices_beat_settings_and_import_splits_comma_list(): void
    {
        $svc = app(SysDictService::class);
        app(\App\Services\Video\VideoSettingService::class)->saveOptions(['filter_area' => '大陆,香港,台湾']);
        \Illuminate\Support\Facades\Cache::forget(\App\Services\Video\VideoSettingService::CACHE_KEY);

        $this->assertSame([], $svc->filterChoices('area'));
        $imp = $svc->importFromSettings('filter_area');
        $this->assertSame(0, $imp['code'], $imp['msg'] ?? '');
        $this->assertSame(3, (int) ($imp['data']['added'] ?? 0));
        $this->assertSame(['大陆', '香港', '台湾'], $svc->filterChoices('area'));

        $again = $svc->importFromSettings('filter_area');
        $this->assertSame(1, $again['code']);

        $this->assertSame(0, $svc->addSysSet('filter_area', '日本', 0, '日本', null, '日本', 1, 0, '')['code']);
        $this->assertContains('日本', $svc->filterChoices('area'));

        $off = $svc->addSysSet('filter_year', '1999', 0, '1999', null, '1999', 0, 1, '');
        $this->assertSame(0, $off['code']);
        $this->assertSame([], $svc->filterChoices('year'));
    }

    public function test_value_types_match_a13_and_reject_no_change(): void
    {
        $svc = app(SysDictService::class);

        $asArray = $svc->addSysSet('cfg', 'obj', 3, '["a"]', null, '对象', 0, 0, '');
        $this->assertSame(1, $asArray['code']);
        $this->assertStringContainsString('JSON对象', $asArray['msg']);

        $asObj = $svc->addSysSet('cfg', 'arr', 4, '{"a":1}', null, '数组', 0, 0, '');
        $this->assertSame(1, $asObj['code']);
        $this->assertStringContainsString('JSON数组', $asObj['msg']);

        $this->assertSame(0, $svc->addSysSet('cfg', 'obj', 3, '{"a":1}', null, '对象', 0, 0, '')['code']);
        $this->assertSame(0, $svc->addSysSet('cfg', 'arr', 4, '["a","b"]', null, '数组', 0, 0, '')['code']);
        $this->assertSame(0, $svc->addSysSet('cfg', 'n', 1, '20', null, '上限', 0, 0, '')['code']);
        $this->assertSame(1, $svc->addSysSet('cfg', 'badn', 1, '3.14', null, '上限', 0, 0, '')['code']);

        $enum = $svc->addSysSet('cfg', 'flag', 5, 'on', '["on","off"]', '开关', 0, 0, '');
        $this->assertSame(0, $enum['code'], $enum['msg'] ?? '');
        $badEnum = $svc->addSysSet('cfg', 'flag2', 5, 'maybe', '["on","off"]', '开关', 0, 0, '');
        $this->assertSame(1, $badEnum['code']);
        $this->assertStringContainsString('限制', $badEnum['msg']);

        $list = $svc->getSysLists('cfg', 'obj', '', 20);
        $row = $list['data']['data'][0] ?? [];
        $this->assertSame('JSON对象', $row['value_type_label'] ?? '');
        $this->assertSame('{"a":1}', $row['dict_value'] ?? '');
        $id = (int) ($row['id'] ?? 0);
        $this->assertGreaterThan(0, $id);

        $same = $svc->updateSysSet($id, 'cfg', 'obj', 3, '{"a":1}', null, '对象', 0, 0, '');
        $this->assertSame(1, $same['code']);
        $this->assertStringContainsString('没有任何改动', $same['msg']);

        $changed = $svc->updateSysSet($id, 'cfg', 'obj', 3, '{"a":2}', null, '对象', 0, 0, '');
        $this->assertSame(0, $changed['code'], $changed['msg'] ?? '');
    }
}

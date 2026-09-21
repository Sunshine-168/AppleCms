<?php

namespace Tests\Unit;

use App\Support\AdminSeedLabel;
use Tests\TestCase;

class AdminSeedLabelTest extends TestCase
{
    public function test_english_maps_seeded_group_and_type_names(): void
    {
        app()->setLocale('en');

        $this->assertSame('Member', AdminSeedLabel::group('普通会员'));
        $this->assertSame('VIP', AdminSeedLabel::group('VIP'));
        $this->assertSame('Ungrouped', AdminSeedLabel::group(''));
        $this->assertSame('Unknown group', AdminSeedLabel::group('', true));
        $this->assertSame('黄金会员', AdminSeedLabel::group('黄金会员'));
        $this->assertSame('Movies', AdminSeedLabel::type('电影'));
        $this->assertSame('TV series', AdminSeedLabel::type('电视剧'));
        $this->assertSame('Variety', AdminSeedLabel::type('综艺'));
        $this->assertSame('Anime', AdminSeedLabel::type('动漫'));
        $this->assertSame('Hub', AdminSeedLabel::kind('hub'));
    }

    public function test_chinese_keeps_seeded_names(): void
    {
        app()->setLocale('zh_cn');

        $this->assertSame('普通会员', AdminSeedLabel::group('普通会员'));
        $this->assertSame('电影', AdminSeedLabel::type('电影'));
        $this->assertSame('频道', AdminSeedLabel::kind('hub'));
    }
}

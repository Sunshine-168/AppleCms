<?php

namespace Tests\Unit;

use App\Support\PlayLineName;
use Tests\TestCase;

class PlayLineNameTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        PlayLineName::flush();
        PlayLineName::remember('__test__', '__test__');
    }

    public function test_hongniu_flags_get_chinese_names(): void
    {
        $this->assertSame('红牛云播', PlayLineName::guess('hnyun'));
        $this->assertSame('红牛直链', PlayLineName::guess('hnm3u8'));
        $this->assertSame('红牛云播', PlayLineName::label('hnyun', 'hnyun'));
        $this->assertSame('红牛直链', PlayLineName::label('hnm3u8', 'hnm3u8'));
    }

    public function test_keeps_admin_custom_names(): void
    {
        $this->assertSame('备用线路', PlayLineName::label('hnyun', '备用线路'));
        $this->assertSame('下载-红牛云播', PlayLineName::label('hnyun', '下载-hnyun'));
    }

    public function test_common_collect_brands(): void
    {
        $this->assertSame('量子直链', PlayLineName::guess('lzm3u8'));
        $this->assertSame('非凡云播', PlayLineName::guess('ffyun'));
        $this->assertSame('腾讯视频', PlayLineName::guess('qq'));
    }

    public function test_flag_detection(): void
    {
        $this->assertTrue(PlayLineName::isFlag('hnyun'));
        $this->assertTrue(PlayLineName::isFlag('hnm3u8'));
        $this->assertFalse(PlayLineName::isFlag('红牛云播'));
        $this->assertFalse(PlayLineName::isFlag('线路1'));
    }
}

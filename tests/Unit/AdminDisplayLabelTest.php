<?php

namespace Tests\Unit;

use App\Services\Admin\System\SysScheduleService;
use App\Services\Admin\Video\SiteModuleService;
use Tests\TestCase;

class AdminDisplayLabelTest extends TestCase
{
    public function test_collect_presets_follow_locale(): void
    {
        $svc = app(SiteModuleService::class);

        app()->setLocale('en');
        $cron = $svc->collectCronPresets();
        $hours = $svc->collectHourPresets();
        $this->assertSame('Hourly', $cron['0 * * * *']);
        $this->assertSame('Updated today', $hours[24]);

        app()->setLocale('zh_cn');
        $cron = $svc->collectCronPresets();
        $hours = $svc->collectHourPresets();
        $this->assertSame('每小时', $cron['0 * * * *']);
        $this->assertSame('当天更新', $hours[24]);
    }

    public function test_schedule_board_chrome_follows_locale(): void
    {
        $svc = app(SysScheduleService::class);

        app()->setLocale('en');
        $en = $svc->pageBoard();
        $this->assertSame('Scheduler', $en['ui']['title']);
        $this->assertSame('Due collect', $en['builtins'][0]['label']);
        $this->assertSame('Hourly', $en['cron_presets']['0 * * * *']);

        app()->setLocale('zh_cn');
        $zh = $svc->pageBoard();
        $this->assertSame('定时任务', $zh['ui']['title']);
        $this->assertSame('到期采集', $zh['builtins'][0]['label']);
        $this->assertSame('每小时', $zh['cron_presets']['0 * * * *']);
        $this->assertSame('加', $zh['ui']['add_prefix']);
        $this->assertSame('百度推送', $zh['presets'][0]['label']);
    }
}

<?php

namespace Plugins\Sms\Jobs;

use App\Support\Plugins\PluginScheduleHandler;
use Illuminate\Support\Facades\Schema;
use Plugins\Sms\Models\SmsCode;

class PruneExpiredCodes implements PluginScheduleHandler
{
    public function handle(): string
    {
        if (! Schema::hasTable('plugin_sms_codes')) {
            return '还没有短信验证码表';
        }
        $n = SmsCode::query()->where('expire_at', '>', 0)->where('expire_at', '<', time())->delete();

        return '删了 '.$n.' 条过期验证码';
    }
}

<?php

namespace Tests\Unit;

use Tests\TestCase;

class PcntlScheduleStubTest extends TestCase
{
    public function test_schedule_namespace_has_pcntl_signal_when_extension_is_blocked(): void
    {
        require_once app_path('Support/PcntlScheduleStub.php');

        if (\function_exists('pcntl_signal')) {
            $this->assertTrue(\function_exists('pcntl_signal'));

            return;
        }

        $this->assertTrue(\function_exists('Illuminate\\Console\\Scheduling\\pcntl_signal'));
        $this->assertTrue(\function_exists('Illuminate\\Console\\Scheduling\\pcntl_async_signals'));
        $this->assertTrue(\Illuminate\Console\Scheduling\pcntl_signal(15, static fn () => null));
    }
}

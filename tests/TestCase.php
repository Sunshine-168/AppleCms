<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsAdmin(): static
    {
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
        Cache::put('admin_user_1', [
            'id' => 1,
            'username' => 'admin',
            'role_id' => 1,
            'status' => 1,
        ], 300);

        return $this;
    }
}

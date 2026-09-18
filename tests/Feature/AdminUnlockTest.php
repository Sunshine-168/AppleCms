<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUnlockTest extends TestCase
{
    use RefreshDatabase;

    private const PLAIN = 'LockOk-12';

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    private function insertAdmin(string $password): void
    {
        $now = time();
        DB::table('sys_user')->insert([
            'id' => 1,
            'username' => 'admin',
            'password' => $password,
            'email' => '',
            'remark' => '超级管理员',
            'role' => 1,
            'role_id' => 0,
            'status' => 1,
            'token' => '',
            'create_time' => $now,
            'update_time' => $now,
        ]);
    }

    public function test_unlock_accepts_the_login_password(): void
    {
        $this->insertAdmin(self::PLAIN);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/unlock', ['password' => self::PLAIN])
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('msg', '已解锁');
    }

    public function test_unlock_accepts_a_hashed_login_password(): void
    {
        $this->insertAdmin(Hash::make(self::PLAIN));

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->postJson('/admin/unlock', ['password' => self::PLAIN])
            ->assertOk()
            ->assertJsonPath('code', 0);
    }

    public function test_unlock_rejects_a_wrong_password(): void
    {
        $this->insertAdmin(self::PLAIN);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->postJson('/admin/unlock', ['password' => 'not-the-login-pass'])
            ->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonPath('msg', '密码不对');
    }

    public function test_login_still_works_with_plaintext_password(): void
    {
        $this->insertAdmin(self::PLAIN);

        $this->withSession(['captcha' => 8])
            ->postJson('/api/admin/login', [
                'username' => 'admin',
                'password' => self::PLAIN,
                'captcha' => '8',
            ])
            ->assertOk()
            ->assertJsonPath('code', 0);
    }

    public function test_login_accepts_a_hashed_password(): void
    {
        $this->insertAdmin(Hash::make(self::PLAIN));

        $this->withSession(['captcha' => 8])
            ->postJson('/api/admin/login', [
                'username' => 'admin',
                'password' => self::PLAIN,
                'captcha' => '8',
            ])
            ->assertOk()
            ->assertJsonPath('code', 0);
    }

    public function test_login_says_when_there_is_no_admin(): void
    {
        $this->withSession(['captcha' => 8])
            ->postJson('/api/admin/login', [
                'username' => 'admin',
                'password' => self::PLAIN,
                'captcha' => '8',
            ])
            ->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonPath('msg', '还没有管理员，请重新安装');
    }
}

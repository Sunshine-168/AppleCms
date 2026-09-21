<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoOption;
use App\Services\Video\VideoSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AppFrontApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_home_and_config_return_json(): void
    {
        $this->getJson('/api/app/home')
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonStructure(['data' => ['site', 'slides', 'types', 'recommend', 'latest', 'hot']]);

        $this->getJson('/api/app/config')
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonStructure(['data' => ['site' => ['title']]]);
    }

    public function test_video_detail_and_search(): void
    {
        $video = VideoModel::query()->create([
            'title' => '对接片',
            'status' => 1,
            'cover' => '/cover.jpg',
            'remarks' => '更新中',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->getJson('/api/app/videos/'.$video->id)
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.video.id', $video->id)
            ->assertJsonPath('data.video.title', '对接片');

        $this->getJson('/api/app/videos/999999')
            ->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonPath('msg', '影片不存在');

        $this->getJson('/api/app/search?wd=对接')
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.list.0.title', '对接片');
    }

    public function test_legacy_app_vod_list_still_works(): void
    {
        $this->getJson('/api/app/vod?ac=list')
            ->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonStructure(['list', 'class']);
    }

    public function test_app_key_rejects_then_accepts(): void
    {
        VideoOption::query()->create([
            'k' => 'app_key',
            'v' => 'app-secret',
            'updated_at' => time(),
        ]);
        Cache::forget(VideoSettingService::CACHE_KEY);

        $this->getJson('/api/app/home')
            ->assertForbidden()
            ->assertJsonPath('msg', '密钥无效');

        $this->getJson('/api/app/home?key=app-secret')
            ->assertOk()
            ->assertJsonPath('code', 0);
    }

    public function test_member_register_login_and_center(): void
    {
        $email = 'app-'.uniqid().'@test.local';
        $smsOn = false;
        try {
            $smsOn = app(\App\Support\Plugins\PluginManager::class)->isEnabled('sms');
        } catch (\Throwable) {
            $smsOn = false;
        }
        if ($smsOn) {
            Member::query()->create([
                'name' => 'App会员',
                'email' => $email,
                'password' => Hash::make('secret12'),
                'status' => 1,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
            $this->postJson('/api/app/member/register', [
                'name' => 'App会员',
                'email' => 'other-'.uniqid().'@test.local',
                'password' => 'secret12',
            ])->assertOk()->assertJsonPath('msg', '请填写手机号和验证码');
        } else {
            $this->postJson('/api/app/member/register', [
                'name' => 'App会员',
                'email' => $email,
                'password' => 'secret12',
            ])->assertOk()->assertJsonPath('code', 0)->assertJsonPath('data.member.email', $email);
        }

        $token = $this->postJson('/api/app/member/login', [
            'email' => $email,
            'password' => 'secret12',
        ])->assertOk()
            ->assertJsonPath('code', 0)
            ->json('data.token');
        $this->assertNotEmpty($token);

        $this->getJson('/api/app/member', ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.member.email', $email);

        Auth::guard('member')->logout();
        $this->flushSession();
        $this->getJson('/api/app/member')
            ->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonPath('msg', '请先登录');
    }

    public function test_favorite_requires_login(): void
    {
        $video = VideoModel::query()->create([
            'title' => '收藏片',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->postJson('/api/app/videos/'.$video->id.'/favorite')
            ->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonPath('msg', '请先登录');

        $member = Member::query()->create([
            'name' => '藏家',
            'email' => 'fav-'.uniqid().'@test.local',
            'password' => Hash::make('secret12'),
            'status' => 1,
            'api_token' => 'tok-fav-1',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->withHeaders(['Authorization' => 'Bearer tok-fav-1'])
            ->postJson('/api/app/videos/'.$video->id.'/favorite')
            ->assertOk()
            ->assertJsonPath('code', 0);
        $this->assertTrue(\App\Models\Member\MemberFavorite::query()
            ->where('member_id', $member->id)
            ->where('video_id', $video->id)
            ->exists());
    }
}

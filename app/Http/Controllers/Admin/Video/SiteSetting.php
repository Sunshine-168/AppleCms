<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Services\Video\VideoSettingService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SiteSetting extends Controller
{
    public function __construct(private readonly VideoSettingService $settings) {}

    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'site');
        if (! in_array($tab, ['site', 'look', 'interact', 'more'], true)) {
            $tab = 'site';
        }

        return view('admin.video.settings', [
            'site' => $this->settings->site(),
            'tab' => $tab,
            'pluginLinks' => app(\App\Plugins\PluginHost::class)->settingsLinks(),
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $data = $this->settings->save($request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function testMail(Request $request): JsonResponse
    {
        $to = trim((string) $request->input('to', ''));
        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return Ajax::message(1, '请填写正确的邮箱');
        }
        $this->settings->applyRuntime();
        try {
            Mail::raw('这是一封 LaraVideo 站点邮件测试，若您收到此信说明 SMTP 配置正常。', function ($message) use ($to) {
                $message->to($to)->subject('邮件测试');
            });
        } catch (\Throwable $e) {
            return Ajax::message(1, $e->getMessage() ?: '发送失败');
        }

        return Ajax::message(0, '已发送测试邮件');
    }

    public function configEmail(): View
    {
        return view('admin.video.config_email', [
            'site' => $this->settings->site(),
        ]);
    }

    public function configPage(string $page): View
    {
        $views = [
            'api' => 'admin.video.config_api',
            'collect' => 'admin.video.config_collect',
            'player' => 'admin.video.config_player',
            'email' => 'admin.video.config_email',
        ];
        if (isset($views[$page])) {
            return view($views[$page], [
                'site' => $this->settings->site(),
            ]);
        }
        $extra = $this->settings->extraPages();
        if (! isset($extra[$page])) {
            abort(404);
        }

        return view('admin.video.config_form', [
            'site' => $this->settings->site(),
            'title' => $extra[$page]['title'],
            'hint' => $extra[$page]['hint'] ?? '',
            'fields' => $extra[$page]['fields'],
        ]);
    }
}

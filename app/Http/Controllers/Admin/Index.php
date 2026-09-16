<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Plugins\PluginManager;
use App\Services\Admin\VideoDashboardService;
use App\Support\AdminNav;
use App\Support\AdminUi;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use App\Support\Captcha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * 后台首页
 */
class Index extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.welcome');
    }

    /**
     * 欢迎页
     */
    public function welcome(VideoDashboardService $board): View|Factory
    {
        return view('admin.welcome', $board->board());
    }

    /**
     * 全部功能目录
     */
    public function more(PluginManager $plugins): View|Factory
    {
        $list = $plugins->listForAdmin();

        return view('admin.more', [
            'catalog' => AdminNav::catalog(),
            'plugins' => array_values(array_filter($list, static fn (array $row): bool => ! empty($row['enabled']))),
            'pluginTotal' => count($list),
        ]);
    }

    public function switchUi(Request $request): RedirectResponse
    {
        $code = (string) $request->input('ui_locale', '');
        if (! AdminUi::isValid($code)) {
            return back()->with('error', admin_t('locale.err_ui'));
        }
        $remembered = AdminUi::remember($code, true);
        AdminUi::apply();

        return back()->with('status', admin_t('locale.msg_admin'))->withCookie($remembered['cookie']);
    }

    /**
     * 欢迎页统计数据
     * @return JsonResponse
     */
    public function welcomeStats(): JsonResponse
    {
        try {
            $counts = app(VideoDashboardService::class)->counts();

            return Ajax::message(0, 'success', $counts);
        } catch (\Throwable) {
            return Ajax::message(0, 'success', [
                'vod_total' => 0,
                'vod_today' => 0,
                'comment_total' => 0,
                'comment_pending' => 0,
                'user_total' => 0,
                'visit_today' => 0,
                'pv_today' => 0,
                'uv_today' => 0,
                'play_today' => 0,
                'report_open' => 0,
                'playfail_open' => 0,
                'gbook_pending' => 0,
                'collect_fail' => 0,
            ]);
        }
    }

    /**
     * 加减法验证码
     */
    public function captcha(): JsonResponse
    {
        $cap = Captcha::generate();

        return Ajax::message(0, 'success', [
            'question' => $cap['question'],
        ]);
    }
}

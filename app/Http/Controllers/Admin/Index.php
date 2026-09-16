<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\Admin\System\SysPermService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * 后台首页
 * @param Request $request
 * @return Factory|View
 */
class Index extends Controller
{
    protected SysPermService $systemPermService;

    public function __construct()
    {
        $this->systemPermService = new SysPermService();
    }
    /**
     * 菜单列表
     * @param Request $request
     * @return Factory|View
     */
    public function index(Request $request): Factory|View
    {
        $uid   = (int) session('admin_uid', 0);
        $menus = $this->systemPermService->getAdminMenus($uid);

        return view('admin.layouts.index', compact('menus'));
    }

    /**
     * 欢迎页
     * @return View|Factory
     */
    public function welcome(): View|Factory
    {
        return view('admin.welcome');
    }

    /**
     * 欢迎页统计数据
     * @return JsonResponse
     */
    public function welcomeStats(): JsonResponse
    {
        try {
            return Ajax::message(0, 'success', [
                'vod_total' => \App\Models\Video\VideoModel::query()->count(),
                'vod_today' => \App\Models\Video\VideoModel::query()->where('created_at', '>=', strtotime('today'))->count(),
                'comment_total' => $this->safeCount('video_comments', fn () => \App\Models\Video\VideoComment::query()->count()),
                'comment_pending' => $this->safeCount('video_comments', fn () => \App\Models\Video\VideoComment::query()->where('status', 0)->count()),
                'user_total' => $this->safeCount('members', fn () => \App\Models\Member\Member::query()->count()),
                'visit_today' => (int) \App\Models\Video\VideoStatModel::query()->sum('hits_day'),
                'play_today' => $this->safeCount('member_histories', fn () => \App\Models\Member\MemberHistory::query()->where('updated_at', '>=', strtotime('today'))->count()),
                'report_open' => $this->safeCount('video_reports', fn () => \App\Models\Video\VideoReport::query()->where('status', 0)->count()),
                'playfail_open' => $this->safeCount('video_play_fails', fn () => \App\Models\Video\VideoPlayFail::query()->where('status', 0)->count()),
                'gbook_pending' => $this->safeCount('video_guestbooks', fn () => \App\Models\Video\VideoGuestbook::query()->where('status', 0)->count()),
                'collect_fail' => $this->safeCount('video_collect_logs', fn () => \App\Models\Video\VideoCollectLog::query()->where('ok', 0)->where('created_at', '>=', strtotime('today'))->count()),
            ]);
        } catch (\Throwable) {
            return Ajax::message(0, 'success', [
                'vod_total' => 0,
                'vod_today' => 0,
                'comment_total' => 0,
                'comment_pending' => 0,
                'user_total' => 0,
                'visit_today' => 0,
                'play_today' => 0,
                'report_open' => 0,
                'playfail_open' => 0,
                'gbook_pending' => 0,
                'collect_fail' => 0,
            ]);
        }
    }

    private function safeCount(string $table, callable $fn): int
    {
        try {
            if (! Schema::hasTable($table)) {
                return 0;
            }

            return (int) $fn();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * 输出验证码图片
     * @param Request $request
     * @return Response
     */
    public function captcha(Request $request): Response
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $phrase = '';
        for ($i = 0; $i < 4; $i++) {
            $phrase .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $request->session()->put('captcha', $phrase);

        if (! function_exists('imagecreatetruecolor')) {
            return response($phrase, 200)->header('Content-Type', 'text/plain');
        }

        $im = imagecreatetruecolor(120, 40);
        $bg = imagecolorallocate($im, 247, 249, 252);
        $fg = imagecolorallocate($im, 29, 33, 41);
        imagefilledrectangle($im, 0, 0, 120, 40, $bg);
        imagestring($im, 5, 28, 12, $phrase, $fg);
        ob_start();
        imagejpeg($im, null, 80);
        $bin = ob_get_clean();
        imagedestroy($im);

        return response($bin, 200)->header('Content-Type', 'image/jpeg');
    }
}

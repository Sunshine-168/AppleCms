<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\System\SysPermService;
use App\Support\Utils\Ajax;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Response;
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
        return Ajax::message(0, 'success', [
            'vod_total' => 0,
            'vod_today' => 0,
            'article_total' => 0,
            'user_total' => 0,
            'visit_today' => 0,
            'play_today' => 0,
        ]);
    }

    /**
     * 输出验证码图片
     * @param Request $request
     * @return Response
     */
    public function captcha(Request $request): Response
    {
        $builder = new CaptchaBuilder;

        $builder->build();

        // 保存验证码到 session
        session(['captcha' => $builder->getPhrase()]);

        return response($builder->get(), 200)

            ->header('Content-Type', 'image/jpeg');
    }
}

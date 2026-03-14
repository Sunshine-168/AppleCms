<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Gregwar\Captcha\CaptchaBuilder;
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
    /**
     * 后台首页
     * @param Request $request
     * @return Factory|View
     */
    public function index(Request $request): Factory|View
    {
        $menus = config('system.admin_menu');

        return view('admin.index', compact('menus'));
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


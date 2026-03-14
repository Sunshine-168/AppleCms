<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

/**
 * 后台首页
 * @param Request $request
 * @return Factory|\Illuminate\View\View
 */
class Index extends Controller
{
    /**
     * 后台首页
     * @param Request $request
     * @return Factory|\Illuminate\View\View
     */
    public function index(Request $request): Factory|View
    {
        return view('admin.index');
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


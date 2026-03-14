<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gregwar\Captcha\CaptchaBuilder;
/**
 * 后台首页
 * @param Request $request
 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
 */
class Index extends Controller
{
    /**
     * 后台首页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('admin.index'); 
    }

    /**
     * 输出验证码图片
     * @return \Illuminate\Http\Response
     */
    public function captcha(Request $request)
    {
        $builder = new CaptchaBuilder;
        $builder->build();

        // 保存验证码到 session
        session(['captcha' => $builder->getPhrase()]);

        return response($builder->get(), 200)
            ->header('Content-Type', 'image/jpeg');
    }
}


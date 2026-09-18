<?php

namespace Plugins\Pay\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Video\SiteFrontService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Plugins\Pay\Services\PayService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PayController extends Controller
{
    public function __construct(
        private readonly PayService $pay,
        private readonly SiteFrontService $front,
    ) {}

    public function index(): View
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            throw new NotFoundHttpException();
        }
        $site = $this->front->bootSite();
        $coupons = [];
        if (class_exists(\Plugins\Coupon\Services\CouponService::class)) {
            try {
                $coupons = app(\Plugins\Coupon\Services\CouponService::class)->wallet((int) $member->id, 'recharge');
            } catch (\Throwable) {
                $coupons = [];
            }
        }

        return view('pay::checkout', [
            'site' => $site,
            'member' => $member,
            'packages' => PayService::PACKAGES,
            'coupons' => $coupons,
            'wechatReady' => $this->pay->wechatReady(),
            'alipayReady' => $this->pay->alipayReady(),
        ]);
    }

    public function create(Request $request): RedirectResponse|View
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            throw new NotFoundHttpException();
        }
        $yuan = (float) $request->input('amount_yuan', 0);
        $channel = (string) $request->input('channel', 'wechat');
        $couponUserId = (int) $request->input('coupon_user_id', 0);
        $result = $this->pay->create($member, $channel, $yuan, $couponUserId);
        if (($result['code'] ?? 1) !== 0) {
            return back()->withErrors(['pay' => $result['msg'] ?? '下单失败'])->withInput();
        }
        $site = $this->front->bootSite();
        $data = is_array($result['data'] ?? null) ? $result['data'] : [];

        return view('pay::pay', [
            'site' => $site,
            'member' => $member,
            'order' => $data,
            'msg' => (string) ($result['msg'] ?? ''),
        ]);
    }

    public function show(int $id): View
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            throw new NotFoundHttpException();
        }
        $order = \App\Models\Member\MemberOrder::query()
            ->where('id', $id)
            ->where('member_id', $member->id)
            ->first();
        if (! $order) {
            throw new NotFoundHttpException();
        }
        $site = $this->front->bootSite();

        return view('pay::status', [
            'site' => $site,
            'member' => $member,
            'order' => $order,
        ]);
    }

    public function notifyWechat(Request $request): Response
    {
        $xml = $request->getContent();

        return response($this->pay->handleWechatNotify($xml), 200, ['Content-Type' => 'text/xml; charset=UTF-8']);
    }

    public function notifyAlipay(Request $request): Response
    {
        $payload = $request->all();

        return response($this->pay->handleAlipayNotify(is_array($payload) ? $payload : []), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function returnAlipay(Request $request): RedirectResponse
    {
        $no = trim((string) $request->query('out_trade_no', ''));
        $order = $this->pay->findOrder($no);
        if ($order) {
            return redirect('/member/pay/'.$order->id);
        }

        return redirect('/member')->with('status', '已返回，到账以回调为准');
    }
}

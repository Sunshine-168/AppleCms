<?php

namespace Plugins\Coupon\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Video\SiteFrontService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Plugins\Coupon\Services\CouponService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CouponController extends Controller
{
    public function __construct(
        private readonly CouponService $coupons,
        private readonly SiteFrontService $front,
    ) {}

    public function index(): View
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            throw new NotFoundHttpException();
        }
        $site = $this->front->bootSite();

        return view('coupon::index', [
            'site' => $site,
            'member' => $member,
            'shop' => $this->coupons->shopWindow((int) $member->id),
            'wallet' => $this->coupons->wallet((int) $member->id),
        ]);
    }

    public function receive(int $id): RedirectResponse
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            throw new NotFoundHttpException();
        }
        $ok = $this->coupons->receive($id, (int) $member->id);
        if (($ok['code'] ?? 1) !== 0) {
            return back()->withErrors(['coupon' => $ok['msg'] ?? '领取失败']);
        }

        return back()->with('status', $ok['msg'] ?? '已领取');
    }
}

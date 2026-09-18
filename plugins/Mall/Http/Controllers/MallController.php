<?php

namespace Plugins\Mall\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Video\SiteFrontService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Plugins\Mall\Services\MallService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MallController extends Controller
{
    public function __construct(
        private readonly MallService $mall,
        private readonly SiteFrontService $front,
    ) {}

    public function index(Request $request): View
    {
        if (! $this->mall->ready()) {
            throw new NotFoundHttpException();
        }
        $site = $this->front->bootSite();
        $type = trim((string) $request->query('type', ''));
        $filters = [];
        if ($type !== '') {
            $filters['type'] = $type;
        }
        $member = Auth::guard('member')->user();

        return view('mall::index', [
            'site' => $site,
            'list' => $this->mall->paginate(24, $filters),
            'hot' => $type === '' ? $this->mall->hotGoods(8) : [],
            'filterType' => $type,
            'member' => $member,
            'points' => $member ? (int) $member->points : null,
        ]);
    }

    public function show(int $id): View
    {
        $row = $this->mall->published($id);
        if (! $row) {
            throw new NotFoundHttpException();
        }
        $site = $this->front->bootSite();
        $ext = MallService::decodeExt($row->ext ?? '');
        $type = MallService::normalizeType((string) ($row->type ?? ''));
        $member = Auth::guard('member')->user();
        $pool = 0;
        if ($type === 'card' && strtolower((string) ($ext['mode'] ?? $ext['card_mode'] ?? '')) === 'assign') {
            $pool = $this->mall->poolRemain($ext);
        }

        return view('mall::show', [
            'site' => $site,
            'goods' => $row,
            'ext' => $ext,
            'type' => $type,
            'member' => $member,
            'points' => $member ? (int) $member->points : null,
            'pool' => $pool,
            'vipDays' => (int) ($ext['days'] ?? $ext['vip_days'] ?? 0),
            'autoCredit' => (int) ($ext['auto_credit'] ?? 0) === 1,
        ]);
    }

    public function orders(): View
    {
        if (! $this->mall->ready()) {
            throw new NotFoundHttpException();
        }
        $member = Auth::guard('member')->user();
        if (! $member) {
            throw new NotFoundHttpException();
        }
        $site = $this->front->bootSite();

        return view('mall::orders', [
            'site' => $site,
            'list' => $this->mall->memberOrders($member),
            'member' => $member,
            'points' => (int) $member->points,
        ]);
    }

    public function buy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            return $this->reply($request, 1, '请先登录', url('/member/login'));
        }
        $extra = [
            'contact' => (string) $request->input('contact', ''),
            'address' => (string) $request->input('address', ''),
        ];
        $data = $this->mall->buy($id, $member, $extra);
        $ok = (int) ($data['code'] ?? 1) === 0;
        $payload = is_array($data['data'] ?? null) ? $data['data'] : [];
        $to = $ok ? url('/mall/orders') : null;

        return $this->reply(
            $request,
            (int) ($data['code'] ?? 1),
            (string) ($data['msg'] ?? ''),
            $to,
            $payload
        );
    }

    /** @param array<string, mixed> $payload */
    private function reply(Request $request, int $code, string $msg, ?string $to, array $payload = []): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return Ajax::message($code, $msg, $payload);
        }
        if ($code === 0) {
            return redirect($to ?: url('/mall'))->with('status', $msg);
        }

        return back()->with('error', $msg)->withInput();
    }
}

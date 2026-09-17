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

    public function index(): View
    {
        if (! $this->mall->ready()) {
            throw new NotFoundHttpException();
        }
        $site = $this->front->bootSite();

        return view('mall::index', [
            'site' => $site,
            'list' => $this->mall->paginate(),
        ]);
    }

    public function show(int $id): View
    {
        $row = $this->mall->published($id);
        if (! $row) {
            throw new NotFoundHttpException();
        }
        $site = $this->front->bootSite();

        return view('mall::show', ['site' => $site, 'goods' => $row]);
    }

    public function buy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            return $this->reply($request, 1, '请先登录', url('/member/login'));
        }
        $data = $this->mall->buy($id, $member);
        $ok = (int) ($data['code'] ?? 1) === 0;

        return $this->reply(
            $request,
            (int) ($data['code'] ?? 1),
            (string) ($data['msg'] ?? ''),
            $ok ? url('/mall') : null,
            is_array($data['data'] ?? null) ? $data['data'] : []
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

        return back()->with('error', $msg);
    }
}

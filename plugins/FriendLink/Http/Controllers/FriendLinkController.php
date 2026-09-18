<?php

namespace Plugins\FriendLink\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Video\SiteFrontService;
use App\Support\Captcha;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Plugins\FriendLink\Services\FriendLinkService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FriendLinkController extends Controller
{
    public function __construct(
        private readonly FriendLinkService $links,
        private readonly SiteFrontService $front,
    ) {}

    public function captcha(): Response
    {
        if (! $this->links->ready()) {
            throw new NotFoundHttpException();
        }

        return Captcha::response();
    }

    public function applyForm(): View
    {
        $this->guardApply();
        $site = $this->front->bootSite();

        return view('friendlink::apply', [
            'site' => $site,
            'cates' => $this->links->listedCates(),
        ]);
    }

    public function applyStore(Request $request): View|RedirectResponse|JsonResponse
    {
        $this->guardApply();
        $res = $this->links->apply($request->all());
        if ($request->expectsJson() || $request->ajax()) {
            return Ajax::message((int) $res['code'], (string) $res['msg'], is_array($res['data'] ?? null) ? $res['data'] : []);
        }
        if ((int) ($res['code'] ?? 1) !== 0) {
            return back()->withInput()->with('error', (string) $res['msg']);
        }
        $site = $this->front->bootSite();

        return view('friendlink::applied', [
            'site' => $site,
            'token' => (string) (($res['data']['edit_token'] ?? '')),
            'msg' => (string) $res['msg'],
        ]);
    }

    public function editForm(string $token): View
    {
        $this->guardEdit();
        $row = $this->links->findByToken($token);
        if (! $row) {
            throw new NotFoundHttpException();
        }
        $site = $this->front->bootSite();

        return view('friendlink::edit', [
            'site' => $site,
            'link' => $row,
            'cates' => $this->links->listedCates(),
            'token' => $token,
        ]);
    }

    public function editStore(Request $request, string $token): RedirectResponse|JsonResponse
    {
        $this->guardEdit();
        $res = $this->links->editByToken($token, $request->all());
        if ($request->expectsJson() || $request->ajax()) {
            return Ajax::message((int) $res['code'], (string) $res['msg'], is_array($res['data'] ?? null) ? $res['data'] : []);
        }
        if ((int) ($res['code'] ?? 1) !== 0) {
            return back()->withInput()->with('error', (string) $res['msg']);
        }

        return redirect('/links/edit/'.$token)->with('status', (string) $res['msg']);
    }

    public function go(Request $request, int $id): RedirectResponse
    {
        $res = $this->links->go($id, (string) $request->ip(), mb_substr((string) $request->userAgent(), 0, 255));
        $url = is_array($res['data'] ?? null) ? (string) ($res['data']['url'] ?? '') : '';
        if ((int) ($res['code'] ?? 1) !== 0 || $url === '') {
            throw new NotFoundHttpException();
        }

        return redirect()->away($url);
    }

    public function hit(Request $request): JsonResponse
    {
        $referer = trim((string) $request->input('referer', $request->header('referer', '')));
        $res = $this->links->recordHit($referer, (string) $request->ip(), mb_substr((string) $request->userAgent(), 0, 255));

        return Ajax::message((int) $res['code'], (string) ($res['msg'] ?? ''), is_array($res['data'] ?? null) ? $res['data'] : []);
    }

    private function guardApply(): void
    {
        if (! $this->links->ready() || $this->links->options()['allow_apply'] !== 1) {
            throw new NotFoundHttpException();
        }
    }

    private function guardEdit(): void
    {
        if (! $this->links->ready() || $this->links->options()['allow_edit'] !== 1) {
            throw new NotFoundHttpException();
        }
    }
}

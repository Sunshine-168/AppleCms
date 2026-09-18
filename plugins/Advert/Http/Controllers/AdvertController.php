<?php

namespace Plugins\Advert\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Plugins\Advert\Services\AdvertService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertController extends Controller
{
    public function __construct(private readonly AdvertService $ads) {}

    public function go(Request $request, int $id): RedirectResponse
    {
        $res = $this->ads->go(
            $id,
            (string) $request->ip(),
            mb_substr((string) $request->userAgent(), 0, 255),
            mb_substr((string) $request->headers->get('referer', $request->fullUrl()), 0, 255)
        );
        $url = is_array($res['data'] ?? null) ? (string) ($res['data']['url'] ?? '') : '';
        if ((int) ($res['code'] ?? 1) !== 0 || $url === '') {
            throw new NotFoundHttpException();
        }

        return redirect()->away($url);
    }
}

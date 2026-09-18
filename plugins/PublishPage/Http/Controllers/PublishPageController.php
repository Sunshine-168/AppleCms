<?php

namespace Plugins\PublishPage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Plugins\PublishPage\Services\PublishService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublishPageController extends Controller
{
    public function __construct(private readonly PublishService $publish) {}

    public function enter(Request $request): RedirectResponse
    {
        return redirect('/')->withCookie($this->publish->enteredCookie($request));
    }

    public function group(string $id): Response
    {
        if (! $this->publish->ready()) {
            throw new NotFoundHttpException();
        }
        $group = $this->publish->group($id);
        if ($group === null) {
            throw new NotFoundHttpException();
        }
        $html = view('publishpage::group', $this->publish->pageData($group))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, private, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}

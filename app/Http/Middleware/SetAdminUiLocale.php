<?php

namespace App\Http\Middleware;

use App\Support\AdminUi;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** 后台界面语言，不影响前台内容语言 */
class SetAdminUiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin') || $request->is('admin/*') || $request->is('api/admin') || $request->is('api/admin/*')) {
            $ui = $request->query('ui');
            if (is_string($ui) && AdminUi::isValid($ui)) {
                $remembered = AdminUi::remember($ui, saveUser: (int) session('admin_uid', 0) > 0);
                AdminUi::apply();
                $response = $next($request);

                return $response->withCookie($remembered['cookie']);
            }
            AdminUi::apply();
        }

        return $next($request);
    }
}

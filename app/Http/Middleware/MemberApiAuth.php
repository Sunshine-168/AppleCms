<?php

namespace App\Http\Middleware;

use App\Models\Member\Member;
use App\Support\AppApi;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class MemberApiAuth
{
    public function handle(Request $request, Closure $next, string $mode = 'required'): Response
    {
        $member = $this->resolve($request);
        if ($member) {
            Auth::guard('member')->setUser($member);
        } elseif ($mode === 'required') {
            return AppApi::fail('请先登录');
        }

        return $next($request);
    }

    private function resolve(Request $request): ?Member
    {
        if (Auth::guard('member')->check()) {
            return Auth::guard('member')->user();
        }
        $token = trim((string) $request->bearerToken());
        if ($token === '') {
            $token = trim((string) $request->header('X-Member-Token', $request->input('token', $request->query('token', ''))));
        }
        if ($token === '' || ! Schema::hasColumn('members', 'api_token')) {
            return null;
        }

        return Member::query()->where('status', 1)->where('api_token', $token)->first();
    }
}

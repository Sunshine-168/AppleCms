<?php

namespace App\Http\Middleware;

use App\Models\System\SysUserModel;
use App\Support\AdminOpLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOperateLog
{
    public function handle(Request $request, Closure $next): Response
    {
        AdminOpLog::beginRequest();
        [$uid, $username] = $this->resolveUser($request);
        $request->attributes->set('admin_uid', $uid);
        $request->attributes->set('admin_username', $username);

        $response = $next($request);
        AdminOpLog::fromSuccessfulRequest($request, $response);

        return $response;
    }

    private function resolveUser(Request $request): array
    {
        $uid = (int) $request->attributes->get('admin_uid', 0);
        $username = (string) $request->attributes->get('admin_username', '');
        if ($uid > 0 && $username !== '') {
            return [$uid, $username];
        }

        $uid = (int) session('admin_uid', 0);
        $username = (string) session('admin_username', '');
        if ($uid > 0 && $username !== '') {
            return [$uid, $username];
        }

        $auth = (string) $request->header('Authorization', '');
        $bearer = '';
        if ($auth !== '' && str_starts_with($auth, 'Bearer ')) {
            $bearer = trim(substr($auth, 7));
        }
        $token = (string) (
            $request->header('token')
            ?: $request->header('X-Token')
            ?: $request->header('x-token')
            ?: $bearer
            ?: $request->input('token', '')
            ?: $request->input('access_token', '')
        );
        if ($token === '') {
            return [0, ''];
        }
        $user = (new SysUserModel())->findByCondition([['token', '=', $token]]);
        if (empty($user)) {
            return [0, ''];
        }

        return [(int) ($user['id'] ?? 0), (string) ($user['username'] ?? '')];
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\System\SysOperateLogModel;
use App\Models\System\SysUserModel;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOperateLog
{   
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startAt = microtime(true);
        [$uid, $username] = $this->resolveUser($request);
        $request->attributes->set('admin_uid', $uid);
        $request->attributes->set('admin_username', $username);

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $this->writeLog($request, null, $startAt, 500, $e->getMessage());
            throw $e;
        }

        $this->writeLog($request, $response, $startAt);

        return $response;
    }

    /**
     * 截取字符串长度
     */
    private function cut(string $value, int $max): string
    {
        if ($max <= 0 || $value === '') 
        {
            return $value;
        }

        return mb_strlen($value, 'UTF-8') > $max ? mb_substr($value, 0, $max, 'UTF-8') : $value;
    }

    /**
     * 是否需要记录日志
     */
    private function shouldLog(Request $request): bool
    {
        if (!$request->is('admin/*') && !$request->is('api/admin/*')) 
        {
            return false;
        }

        $method = strtoupper($request->method());

        if (in_array($method, ['GET', 'HEAD'], true)) 
        {
            return false;
        }

        // 登录请求不记录
        if ($request->is('admin/login') || $request->is('api/admin/login')) 
        {
            return false;
        }

        return true;
    }

    /**
     * 过滤敏感数据
     */
    private function filterSensitiveData(array $data): array
    {
        $sensitiveKeys = ['password','pwd','token','access_token'];
        foreach ($sensitiveKeys as $key) 
        {
            if (array_key_exists($key, $data))
            {
                $data[$key] = '***';
            }
        }
        return $data;
    }

    private function resolveUser(Request $request): array
    {
        $uid = (int) $request->attributes->get('admin_uid', 0);
        $username = (string) $request->attributes->get('admin_username', '');

        if ($uid > 0 && $username !== '')
        {
            return [$uid, $username];
        }

        $uid = (int) session('admin_uid', 0);
        $username = (string) session('admin_username', '');

        if ($uid > 0 && $username !== '')
        {
            return [$uid, $username];
        }

        $auth = (string) $request->header('Authorization', '');
        $bearer = '';
        if ($auth !== '' && str_starts_with($auth, 'Bearer '))
        {
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

        if ($token === '')
        {
            return [0, ''];
        }

        $user = (new SysUserModel())->findByCondition([['token', '=', $token]]);
        if (empty($user))
        {
            return [0, ''];
        }

        $uid = (int) ($user['id'] ?? 0);
        $username = (string) ($user['username'] ?? '');

        return [$uid, $username];
    }

    /**
     * 写入日志
     */
    private function writeLog(Request $request, ?Response $response, float $startAt, ?int $fallbackCode = null, string $fallbackMsg = ''): void
    {
        if (!$this->shouldLog($request)) 
        {
            return;
        }

        $durationMs = (int) round((microtime(true) - $startAt) * 1000);
        $ip         = (string) $request->ip();

        $route = $request->route() ? (string) $request->route()->uri() : '';

        $pathSegments = array_values(array_filter(explode('/', $request->path())));

        $module = $pathSegments[0] === 'admin' ? ($pathSegments[1] ?? 'admin') : ($pathSegments[2] ?? 'admin');

        $title = '后台操作';
        $path  = $request->path();
        // 根据路径设置标题
        if ($path === 'admin/user/add') $title = '新增系统用户';
        elseif ($path === 'admin/user/update') $title = '更新系统用户';
        elseif ($path === 'admin/user/delete') $title = '删除系统用户';

        $requestData = $this->filterSensitiveData($request->all());
        $requestJson = $requestData ? json_encode($requestData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';

        $responseCode = $fallbackCode ?? ($response ? $response->getStatusCode() : 0);
        $responseMsg  = $fallbackMsg;

        if ($response instanceof JsonResponse) 
        {
            $payload = $response->getData(true);
            if (is_array($payload)) 
            {
                if (isset($payload['code']) && is_numeric($payload['code'])) 
                {
                    $responseCode = (int) $payload['code'];
                }

                if (isset($payload['msg']) && is_string($payload['msg'])) 
                {
                    $responseMsg = $payload['msg'];
                }
            }
        }

        if ($responseMsg === '') 
        {
            $responseMsg = $response ? (string) $response->getStatusCode() : '';
        }

        [$uid, $username] = $this->resolveUser($request);

        // 请求状态判断
        $status = ($responseCode === 0 || ($responseCode >= 200 && $responseCode < 300)) ? 1 : 0;

        // 操作对象
        $targetType = '';
        $targetId   = 0;
        
        if (str_starts_with($path, 'admin/user/')) 
        {
            $targetType = 'sys_user';
            $targetId   = (int) $request->input('id', 0);
        }

        // 构造日志数据
        $insert = [
            'uid'           => $uid,
            'username'      => $this->cut($username, 50),
            'title'         => $this->cut($title, 100),
            'permission'    => $this->cut($route ?: $path, 100),
            'module'        => $this->cut($module, 50),
            'method'        => $this->cut(strtoupper($request->method()), 10),
            'url'           => $this->cut($request->fullUrl(), 255),
            'route'         => $this->cut($route ?: $path, 150),
            'request_data'  => $requestJson,
            'response_code' => $responseCode,
            'response_msg'  => $this->cut($responseMsg, 255),
            'status'        => $status,
            'duration_ms'   => $durationMs,
            'login_ip'      => $ip,
            'ip_address'    => $this->cut(\App\Support\Utils\IpAddress::region((string) $ip), 255),
            'user_agent'    => $this->cut((string) $request->userAgent(), 255),
            'referer'       => $this->cut((string) $request->headers->get('referer', ''), 255),
            'target_type'   => $targetType,
            'target_id'     => $targetId,
            'create_time'   => time(),
            'update_time'   => time(),
        ];

        // 写入数据库
        try {
            SysOperateLogModel::create($insert);
        } catch (\Throwable) {
        }
    }
}

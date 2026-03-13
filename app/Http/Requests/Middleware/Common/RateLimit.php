<?php
namespace App\Http\Middleware\Common;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class RateLimit
{
    protected ?object $redis;

    // 最大请求数
    protected mixed $maxRequests;

    // 时间窗口(秒)
    protected mixed $window;

    // 是否启用限流
    protected mixed $enable;

    public function __construct()
    {
        // 从配置中获取限流参数
        $this->maxRequests = config('rate_limit.max_requests', 60);
        $this->window      = config('rate_limit.window', 60);
        $this->enable      = config('rate_limit.enable', false);
        $this->redis       = Cache::store('redis')->handler();
    }

    public function handle(Request $request, Closure $next): Response
    {

        //关闭限流
        if (empty($this->enable))
        {
            return $next($request);
        }

        $key = $this->getKey($request);

        // 使用Redis的Lua脚本保证原子性操作
        $lua = <<<LUA
local key = KEYS[1]
local limit = tonumber(ARGV[1])
local window = tonumber(ARGV[2])
local current = redis.call('GET', key) or 0

if tonumber(current) >= limit then
    return 0
else
    redis.call('INCR', key)
    if tonumber(current) == 0 then
        redis.call('EXPIRE', key, window)
    end
    return 1
end
LUA;

        $result = $this->redis->eval($lua, [$key, $this->maxRequests, $this->window], 1);

        if (!$result)
        {
            // 返回429 Too Many Requests
            $response = Response::create('请求过于频繁，请稍后再试', 'json', 429);

            $response->header([
                'X-RateLimit-Limit'     => $this->maxRequests,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset'     => $this->redis->ttl($key)
            ]);

            return $response;
        }

        $remaining  = $this->maxRequests - $this->redis->get($key);
        $response   = $next($request);

        // 添加限流头部信息
        $response->header([
            'X-RateLimit-Limit'     => $this->maxRequests,
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset'     => $this->redis->ttl($key)
        ]);

        return $response;
    }

    protected function getKey($request): string
    {
        // 可以根据需要自定义key，例如按IP限流或按用户ID限流
        return 'rate_limit:' . md5($request->ip());

        // 如果是按用户限流，可以使用:
        // return 'rate_limit:' . ($request->user->id ?? $request->ip());
    }
}
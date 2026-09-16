<?php
namespace App\Support;
use Illuminate\Support\Facades\Request;


/**
 * 用户公用逻辑
 */
trait UserTrait
{
    /**
     * 获取用户信息
     * @param string $field
     * @param string $token
     * @return array|string
     */
    public function userByHeader(string $field = '*', string $token = ''): array|string
    {

        $token      = Request::header('Accept-Token', $token);

        if(empty($token))
        {
            return [];
        }

        $where   = [];

        $where[] = ['token', '=', $token];

        if ($field == '*')
        {
            $user    = $this->findByCondition($where, $field);
        }
        elseif (count(explode(',',$field)) > 1)
        {
            $user    = $this->findByCondition($where, $field);
        }
        else
        {
            $user    = $this->valueByCondition($where, $field);
        }

        if(empty($user))
        {
            return [];
        }
        else
        {
            // 在线用户心跳统计 - 可通过配置关闭
            $enableOnlineTracking = env('ENABLE_ONLINE_TRACKING', true);

            if ($enableOnlineTracking)
            {
                $OnlineUser = new OnlineUserTTL();

                // 严格验证用户 ID 有效性
                $userId = null;
                if (is_array($user) && isset($user['id']) && $user['id'] > 0)
                {
                    $userId = $user['id'];
                }
                elseif (is_numeric($user) && $user > 0)
                {
                    $userId = $user;
                }

                // 只有有效的用户 ID 才记录心跳
                if ($userId !== null)
                {
                    $OnlineUser->heartbeat($userId);
                }
            }
        }

        return $user;
    }

    /**
     * 获取当前用户 ID
     */
    public function getUserId(): array|string
    {
        $user = $this->userByHeader('id');

        if (is_array($user)) {
            return $user['id'] ?? '';
        }

        return $user;
    }

    /**
     * 获取当前用户信息
     */
    public function user(): array|string
    {
        return $this->userByHeader('*');
    }

    /**
     * 获取指定字段值
     */
    public function val(string|array $key, $default = null)
    {

        $user = $this->userByHeader('*');

        if (empty($user))
        {
            return is_array($key) ? array_fill_keys($key, $default) : $default;
        }

        if (is_array($key))
        {
            $result = [];

            foreach ($key as $k)
            {
                $result[$k] = $user[$k] ?? $default;
            }

            return $result;
        }

        return $user[$key] ?? $default;
    }

}

<?php
namespace App\Traits;

use Illuminate\Support\Facades\Request;


/**
 * 用户公用逻辑
 */
trait UserInfoTrait
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
            $user    = $this->findByCondition($where, $field,[],true);
        }
        elseif (count(explode(',',$field)) > 1)
        {
            $user    = $this->findByCondition($where, $field, [],true);
        }
        else
        {
            $user    = $this->valueByCondition($where, $field, true);
        }

        if(empty($user))
        {
            return [];
        }

        return $user;
    }

    /**
     * 获取当前用户 ID
     */
    public function getUserId(): ?int
    {
        $user = $this->userByHeader('id');
        if (is_array($user)) {
            return isset($user['id']) ? (int) $user['id'] : null;
        }

        return is_numeric($user) ? (int) $user : null;
    }

    /**
     * 获取当前用户信息
     */
    public function user(): array
    {
        return $this->userByHeader('*');
    }

    /**
     * 获取指定字段值
     */
    public function get(string|array $key, $default = null)
    {

        $user = $this->userByHeader('*');

        if (empty($user)) {
            return is_array($key) ? array_fill_keys($key, $default) : $default;
        }

        if (is_array($key)) {
            $result = [];
            foreach ($key as $k) {
                $result[$k] = $user[$k] ?? $default;
            }
            return $result;
        }

        return $user[$key] ?? $default;

    }
}
<?php
namespace Utils;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Config;

/**
 * token模块
 * Class Token
 * @package app\admin\module\admin
 */
class Token
{

    /**
     * Token配置
     * @return array
     */
    public static function config(): array
    {
        return [
            'token_key'         => Config::get('settings.token_key'),
            'token_expires'     => Config::get('settings.token_expires')
        ];
    }

    /**
     * Token生成
     * @param int $id
     * @param $loginTime
     * @param $loginIp
     * @return string
     */
    public static function create(int $id, $loginTime, $loginIp): string
    {
        $config      = self::config();
        // token密钥
        $tokenKey    = $config['token_key'];
        // token过期时间
        $tokenExpire = $config['token_expires'];

        // 签发时间
        $iat = time();
        // 生效时间
        $nbf = time();
        // 过期时间
        $exp = time() + $tokenExpire * 86400;

        // token生成规则：用户ID + 登录时间 + 登录IP
        $data = [
            'id'            => $id,
            'login_time'    => $loginTime,
            'login_ip'      => $loginIp,
        ];

        $payload = [
            'iat'  => $iat,
            'nbf'  => $nbf,
            'exp'  => $exp,
            'data' => $data,
        ];

        return JWT::encode($payload, $tokenKey, 'HS256');
    }

    /**
     * Token验证
     * @param string $token token
     */
    public static function verify(string $token): array
    {
        try
        {
            // 获取config配置下的token密钥
            $config   = self::config();
            $tokenKey = $config['key'];
            // 解析token
            $decode   = JWT::decode($token, new Key($tokenKey, 'HS256'));
            // 获取用户ID
            $id       = $decode->data->id;

            return Result::success(['id' => $id]);
        }
        catch (\Exception $e)
        {
            Syslog::exception('utils', $e);
            return Result::fail('token 失效');
        }


    }

    /**
     * Token用户id
     * @param string $token token
     * @return array
     */
    public static function id(string $token): array
    {
        if (empty($token))
        {
            return Result::fail('token 不能为空');
        }

        try
        {
            $config   = self::config();
            // 获取config配置下的token密钥
            $tokenKey = $config['key'];
            // 解析token
            $decode   = JWT::decode($token, new Key($tokenKey, 'HS256'));
            // 获取用户ID
            $id       = $decode->data->id;
        }
        catch (\Exception $e)
        {
            Syslog::exception('utils', $e);
            return Result::fail('token 失效');
        }

        return Result::success(['id' => $id]);
    }
}

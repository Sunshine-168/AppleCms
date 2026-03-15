<?php
namespace App\Support\Utils;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class Syslog
{
    /**
     * 写入异常日志
     * @param string $channel 日志通道
     * @param Throwable $e
     * @param array|string $extra 附加信息
     * @return string 返回日志消息
     */
    public static function exception(string $channel, Throwable $e, array|string $extra = ''): string
    {
        $logData = [
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'extra'   => $extra,
        ];

        $msg = json_encode($logData, JSON_UNESCAPED_UNICODE);
        self::write($channel, $msg, 'error');
        self::writeToSystemLog('error', $channel, $e->getMessage(), [], $extra, $e);

        return $msg;
    }

    /**
     * 写入普通日志
     * @param string $channel 日志通道
     * @param string $msg 日志信息
     * @param array $context 关联数据，可选
     * @param string $type 日志级别 info|error|notice...
     */
    public static function log(string $channel, string $msg, array $context = [], string $type = 'info'): void
    {
        if ($context)
        {
            $msg .= ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        self::write($channel, $msg, $type);
        self::writeToSystemLog($type, $channel, $msg, $context);
    }

    /**
     * 内部写日志方法
     * @param string $channel
     * @param string $msg
     * @param string $type
     */
    private static function write(string $channel, string $msg, string $type): void
    {
        try
        {
            Log::channel($channel)->log($type, $msg);
        } catch (InvalidArgumentException)
        {
            // Fallback to default channel when a custom channel is not configured.
            Log::log($type, '[' . $channel . '] ' . $msg);
        }
    }
    /**
     * 写入系统日志
     * @param string $level 日志级别
     * @param string $channel 日志通道
     * @param string $message 日志信息
     * @param array $context 关联数据，可选
     * @param array|string $extra 附加信息
     * @param Throwable|null $e 异常对象，可选
     */
    private static function writeToSystemLog(string $level, string $channel, string $message, array $context = [], array|string $extra = '', ?Throwable $e = null): void
    {
        try
        {
            $requestId = '';
            $method    = '';
            $url       = '';
            $ip        = '';
            $userAgent = '';
            $module    = '';
            $uid       = 0;
            $username  = '';

            if (app()->bound('request'))
            {
                $req = request();
                $requestId = (string) ($req->headers->get('X-Request-Id') ?: $req->headers->get('Request-Id') ?: $req->input('request_id', ''));
                if ($requestId === '')
                {
                    $requestId = (string) $req->attributes->get('request_id', '');
                }
                if ($requestId === '')
                {
                    $requestId = (string) Str::uuid();
                    $req->attributes->set('request_id', $requestId);
                }

                $method    = strtoupper((string) $req->method());
                $url       = (string) $req->fullUrl();
                $ip        = (string) $req->ip();
                $userAgent = (string) $req->userAgent();

                $segments = $req->segments();
                $module = (string) ($segments[0] ?? '');
                if ($module === 'api')
                {
                    $module = (string) ($segments[1] ?? 'api');
                }
                if ($module === '')
                {
                    $module = 'web';
                }

                $uid      = (int) $req->attributes->get('admin_uid', 0);
                $username = (string) $req->attributes->get('admin_username', '');

                if ($uid <= 0 || $username === '')
                {
                    $uid      = (int) session('admin_uid', 0);
                    $username = (string) session('admin_username', '');
                }

                if ($uid <= 0 || $username === '')
                {
                    $auth = (string) $req->header('Authorization', '');
                    $bearer = '';
                    if ($auth !== '' && str_starts_with($auth, 'Bearer '))
                    {
                        $bearer = trim(substr($auth, 7));
                    }

                    $token = (string) (
                        $req->header('token')
                        ?: $req->header('X-Token')
                        ?: $req->header('x-token')
                        ?: $bearer
                        ?: $req->input('token', '')
                        ?: $req->input('access_token', '')
                    );
                    if ($token !== '')
                    {
                        $u = DB::table('sys_user')->where('token', $token)->first();
                        if ($u)
                        {
                            $uid = (int) ($u->id ?? 0);
                            $username = (string) ($u->username ?? '');
                        }
                    }
                }
            }

            if ($requestId === '')
            {
                $requestId = (string) Str::uuid();
            }

            $exceptionClass   = $e ? get_class($e) : '';
            $exceptionMessage = $e ? (string) $e->getMessage() : '';
            $file             = $e ? (string) $e->getFile() : '';
            $line             = $e ? (int) $e->getLine() : 0;
            $trace            = $e ? (string) $e->getTraceAsString() : '';

            $contextJson = $context ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
            $extraJson   = $extra === '' ? '' : (is_string($extra) ? $extra : json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            DB::table('sys_system_log')->insert([
                'level'             => (string) $level,
                'channel'           => (string) $channel,
                'module'            => (string) $module,
                'message'           => (string) $message,
                'context'           => (string) $contextJson,
                'extra'             => (string) $extraJson,
                'exception_class'   => (string) $exceptionClass,
                'exception_message' => (string) $exceptionMessage,
                'file'              => (string) $file,
                'line'              => (int) $line,
                'trace'             => (string) $trace,
                'request_id'        => (string) $requestId,
                'method'            => (string) $method,
                'url'               => (string) $url,
                'ip'                => (string) $ip,
                'user_agent'        => (string) $userAgent,
                'uid'               => (int) $uid,
                'username'          => (string) $username,
                'create_time'       => time(),
                'update_time'       => time(),
            ]);
        } catch (Throwable)
        {
        }
    }
}

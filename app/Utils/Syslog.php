<?php
namespace App\Utils;

use Exception;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class Syslog
{
    /**
     * 写入异常日志
     * @param string $channel 日志通道
     * @param Exception $e
     * @param array|string $extra 附加信息
     * @return string 返回日志消息
     */
    public static function exception(string $channel, Exception $e, array|string $extra = ''): string
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
}

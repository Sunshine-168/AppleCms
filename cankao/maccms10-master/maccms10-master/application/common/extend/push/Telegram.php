<?php
namespace app\common\extend\push;

/**
 * Telegram Bot 推送。
 *
 * 用 parse_mode=HTML，所以正文里的每一个动态片段都必须转义 ——
 * 指标键里有 `|`、路径里可能有 `<`，不转义会让 Telegram 直接拒收整条消息
 * （告警在最需要它的时候静默失败，这是最糟的失败模式）。
 */
class Telegram
{
    public $name = 'Telegram';
    public $ver = '1.0';

    public function submit($title, $content, array $context = [], array $config = [])
    {
        $token = isset($config['telegram_token']) ? trim((string)$config['telegram_token']) : '';
        $chatId = isset($config['telegram_chat_id']) ? trim((string)$config['telegram_chat_id']) : '';
        if ($token === '' || $chatId === '') {
            return ['code' => 905, 'msg' => 'telegram_token / telegram_chat_id not configured'];
        }

        $text = self::renderHtml($title, $content);

        $body = json_encode([
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            return ['code' => 906, 'msg' => 'failed to encode telegram payload'];
        }

        $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
        $res = PushHttp::post($url, $body, PushHttp::jsonHeaders());
        return ['code' => $res['code'], 'msg' => $res['msg']];
    }

    /**
     * 纯函数，供测试对照转义行为。
     *
     * @param string $title
     * @param string $content
     * @return string
     */
    public static function renderHtml($title, $content)
    {
        $t = htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8');
        $c = htmlspecialchars((string)$content, ENT_QUOTES, 'UTF-8');
        return '<b>' . $t . '</b>' . "\n" . $c;
    }
}

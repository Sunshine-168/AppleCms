<?php
namespace app\common\extend\push;

/**
 * 钉钉自定义机器人推送。
 *
 * 钉钉要求机器人至少启用「自定义关键词 / 加签 / IP 白名单」三者之一，
 * 否则会拒收。这里实现加签（最安全，也不依赖消息内容里必须出现某个词）。
 * 后台需要提示站长这一点，否则配好了却收不到消息会一头雾水。
 */
class Dingtalk
{
    public $name = 'DingTalk';
    public $ver = '1.0';

    public function submit($title, $content, array $context = [], array $config = [])
    {
        $token = isset($config['dingtalk_token']) ? trim((string)$config['dingtalk_token']) : '';
        if ($token === '') {
            return ['code' => 905, 'msg' => 'dingtalk_token not configured'];
        }
        $secret = isset($config['dingtalk_secret']) ? trim((string)$config['dingtalk_secret']) : '';

        $url = 'https://oapi.dingtalk.com/robot/send?access_token=' . urlencode($token);
        if ($secret !== '') {
            // 钉钉的时间戳是毫秒
            $timestamp = (string)(time() * 1000);
            $url .= '&timestamp=' . $timestamp . '&sign=' . urlencode(self::sign($timestamp, $secret));
        }

        $body = json_encode([
            'msgtype'  => 'markdown',
            'markdown' => [
                'title' => (string)$title,
                'text'  => '### ' . (string)$title . "\n\n" . (string)$content,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            return ['code' => 906, 'msg' => 'failed to encode dingtalk payload'];
        }

        $res = PushHttp::post($url, $body, PushHttp::jsonHeaders());
        if ($res['code'] === 1) {
            // 钉钉即使拒收也可能返回 HTTP 200，真正的结果在 body 的 errcode 里
            $json = json_decode($res['body'], true);
            if (is_array($json) && isset($json['errcode']) && intval($json['errcode']) !== 0) {
                return ['code' => intval($json['errcode']), 'msg' => isset($json['errmsg']) ? (string)$json['errmsg'] : 'dingtalk error'];
            }
        }
        return ['code' => $res['code'], 'msg' => $res['msg']];
    }

    /**
     * 钉钉加签：base64(HMAC-SHA256("{timestamp}\n{secret}", secret))
     *
     * 纯函数，供测试对照已知向量。
     *
     * @param string $timestamp 毫秒时间戳
     * @param string $secret
     * @return string
     */
    public static function sign($timestamp, $secret)
    {
        $stringToSign = (string)$timestamp . "\n" . (string)$secret;
        return base64_encode(hash_hmac('sha256', $stringToSign, (string)$secret, true));
    }
}

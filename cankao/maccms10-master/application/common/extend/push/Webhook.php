<?php
namespace app\common\extend\push;

/**
 * 通用 Webhook 推送：POST 一段结构化 JSON 给站长自己的接收端。
 *
 * 带 HMAC-SHA256 签名头，让接收端能验证请求确实来自本站，
 * 而不是任何知道 URL 的人都能伪造告警。
 */
class Webhook
{
    public $name = 'Webhook';
    public $ver = '1.0';

    /**
     * @param string $title
     * @param string $content
     * @param array  $context 结构化字段，直接进 JSON
     * @param array  $config  monitor 配置段
     * @return array ['code'=>1|非1,'msg'=>string]
     */
    public function submit($title, $content, array $context = [], array $config = [])
    {
        $url = isset($config['webhook_url']) ? trim((string)$config['webhook_url']) : '';
        if ($url === '') {
            return ['code' => 905, 'msg' => 'webhook_url not configured'];
        }

        $payload = array_merge([
            'title'   => (string)$title,
            'content' => (string)$content,
            'site'    => isset($GLOBALS['config']['site']['site_name']) ? (string)$GLOBALS['config']['site']['site_name'] : '',
            'ts'      => time(),
        ], $context);

        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            return ['code' => 906, 'msg' => 'failed to encode webhook payload'];
        }

        $headers = PushHttp::jsonHeaders();
        $secret = isset($config['webhook_secret']) ? trim((string)$config['webhook_secret']) : '';
        if ($secret !== '') {
            $headers[] = 'X-Maccms-Signature: sha256=' . hash_hmac('sha256', $body, $secret);
        }

        $res = PushHttp::post($url, $body, $headers);
        return ['code' => $res['code'], 'msg' => $res['msg']];
    }

    /**
     * 供测试对照的签名计算。
     *
     * @param string $body
     * @param string $secret
     * @return string
     */
    public static function sign($body, $secret)
    {
        return 'sha256=' . hash_hmac('sha256', (string)$body, (string)$secret);
    }
}

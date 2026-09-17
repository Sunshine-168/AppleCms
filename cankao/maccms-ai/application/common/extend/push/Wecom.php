<?php
namespace app\common\extend\push;

/**
 * 企业微信群机器人推送。
 */
class Wecom
{
    public $name = 'WeCom';
    public $ver = '1.0';

    public function submit($title, $content, array $context = [], array $config = [])
    {
        $key = isset($config['wecom_key']) ? trim((string)$config['wecom_key']) : '';
        if ($key === '') {
            return ['code' => 905, 'msg' => 'wecom_key not configured'];
        }

        $url = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=' . urlencode($key);
        $body = json_encode([
            'msgtype'  => 'markdown',
            'markdown' => ['content' => '### ' . (string)$title . "\n" . (string)$content],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            return ['code' => 906, 'msg' => 'failed to encode wecom payload'];
        }

        $res = PushHttp::post($url, $body, PushHttp::jsonHeaders());
        if ($res['code'] === 1) {
            // 企微拒收时也返回 HTTP 200，真正的结果在 body 的 errcode 里
            $json = json_decode($res['body'], true);
            if (is_array($json) && isset($json['errcode']) && intval($json['errcode']) !== 0) {
                return ['code' => intval($json['errcode']), 'msg' => isset($json['errmsg']) ? (string)$json['errmsg'] : 'wecom error'];
            }
        }
        return ['code' => $res['code'], 'msg' => $res['msg']];
    }
}

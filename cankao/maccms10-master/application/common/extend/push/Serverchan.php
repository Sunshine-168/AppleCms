<?php
namespace app\common\extend\push;

/**
 * Server 酱（sctapi.ftqq.com）推送，微信收告警最省事的一条路。
 */
class Serverchan
{
    public $name = 'ServerChan';
    public $ver = '1.0';

    public function submit($title, $content, array $context = [], array $config = [])
    {
        $key = isset($config['serverchan_key']) ? trim((string)$config['serverchan_key']) : '';
        if ($key === '') {
            return ['code' => 905, 'msg' => 'serverchan_key not configured'];
        }

        $url = 'https://sctapi.ftqq.com/' . rawurlencode($key) . '.send';
        $body = http_build_query([
            'title' => (string)$title,
            'desp'  => (string)$content,
        ]);

        $res = PushHttp::post($url, $body, ['Content-Type: application/x-www-form-urlencoded']);
        return ['code' => $res['code'], 'msg' => $res['msg']];
    }
}

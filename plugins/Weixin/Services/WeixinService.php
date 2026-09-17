<?php

namespace Plugins\Weixin\Services;

use App\Models\Video\VideoModel;
use App\Services\Video\VideoSettingService;
use Illuminate\Support\Facades\Schema;

class WeixinService
{
    public function __construct(private readonly VideoSettingService $settings) {}

    public function token(): string
    {
        return trim((string) $this->settings->get('weixin_token', ''));
    }

    public function checkSignature(string $signature, string $timestamp, string $nonce): bool
    {
        $token = $this->token();
        if ($token === '' || $signature === '') {
            return false;
        }
        $tmp = [$token, $timestamp, $nonce];
        sort($tmp, SORT_STRING);

        return hash_equals(sha1(implode('', $tmp)), $signature);
    }

    public function reply(string $xml): string
    {
        $data = $this->fromXml($xml);
        if ($data === []) {
            return 'success';
        }
        $type = (string) ($data['MsgType'] ?? '');
        $from = (string) ($data['FromUserName'] ?? '');
        $to = (string) ($data['ToUserName'] ?? '');
        if ($from === '' || $to === '') {
            return 'success';
        }
        if ($type === 'event') {
            $event = strtolower((string) ($data['Event'] ?? ''));
            if ($event === 'subscribe') {
                return $this->text($from, $to, '欢迎关注。回复片名可搜索本站影片。');
            }

            return 'success';
        }
        if ($type === 'text') {
            $wd = trim((string) ($data['Content'] ?? ''));
            if ($wd === '') {
                return $this->text($from, $to, '请输入片名');
            }

            return $this->text($from, $to, $this->search($wd));
        }

        return 'success';
    }

    private function search(string $wd): string
    {
        if (! Schema::hasTable('videos')) {
            return '站点影片表还没就绪';
        }
        $rows = VideoModel::query()->published()
            ->where('title', 'like', '%'.$wd.'%')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'title']);
        if ($rows->isEmpty()) {
            return '没有找到「'.$wd.'」';
        }
        $lines = [];
        foreach ($rows as $row) {
            $lines[] = $row->title.' '.url('/vod/'.$row->id);
        }

        return implode("\n", $lines);
    }

    private function text(string $to, string $from, string $content): string
    {
        $now = time();
        $content = str_replace(']]>', ']] >', $content);

        return '<xml><ToUserName><![CDATA['.$to.']]></ToUserName><FromUserName><![CDATA['.$from.']]></FromUserName><CreateTime>'.$now.'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$content.']]></Content></xml>';
    }

    /** @return array<string, string> */
    private function fromXml(string $xml): array
    {
        $xml = trim($xml);
        if ($xml === '') {
            return [];
        }
        $prev = libxml_use_internal_errors(true);
        $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        libxml_use_internal_errors($prev);
        if ($obj === false) {
            return [];
        }
        $json = json_encode($obj);
        $arr = is_string($json) ? json_decode($json, true) : [];
        if (! is_array($arr)) {
            return [];
        }
        $out = [];
        foreach ($arr as $k => $v) {
            $out[(string) $k] = is_scalar($v) ? (string) $v : '';
        }

        return $out;
    }
}

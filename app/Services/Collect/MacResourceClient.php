<?php

namespace App\Services\Collect;

use Illuminate\Support\Facades\Http;

class MacResourceClient
{
    /**
     * @param  array<string, mixed>  $query
     * @return array{ok:bool,msg:string,format:string,page:array,types:array,list:array}
     */
    public function fetch(string $apiUrl, array $query = [], string $prefer = 'auto'): array
    {
        $apiUrl = trim($apiUrl);
        if ($apiUrl === '' || ! preg_match('#^https?://#i', $apiUrl)) {
            return $this->fail('采集地址无效');
        }

        $url = $this->buildUrl($apiUrl, $query);
        $body = $this->httpGet($url);
        if ($body === '') {
            return $this->fail('接口无返回：'.$url);
        }

        $prefer = strtolower($prefer);
        if (in_array($prefer, ['json', 'xml'], true)) {
            $parsed = $prefer === 'json' ? $this->parseJson($body) : $this->parseXml($body);
        } else {
            $parsed = $this->parseJson($body);
            if (! ($parsed['ok'] ?? false)) {
                $parsed = $this->parseXml($body);
            }
        }

        if (! ($parsed['ok'] ?? false)) {
            return $this->fail($parsed['msg'] ?? '无法解析资源接口');
        }
        $parsed['page']['url'] = $url;

        return $parsed;
    }

    /** @param  array<string, mixed>  $query */
    public function buildUrl(string $apiUrl, array $query): string
    {
        $query = array_filter($query, fn ($v) => $v !== null && $v !== '');
        $join = str_contains($apiUrl, '?') ? '&' : '?';

        return $apiUrl.$join.http_build_query($query);
    }

    private function httpGet(string $url): string
    {
        $response = Http::timeout(30)
            ->withHeaders(['User-Agent' => 'LaraVideo-Collector/1.0'])
            ->get($url);
        if (! $response->successful()) {
            return '';
        }

        return (string) $response->body();
    }

    /** @return array<string, mixed> */
    private function parseJson(string $body): array
    {
        $json = json_decode($body, true);
        if (! is_array($json) || ! isset($json['list'])) {
            return $this->fail('JSON 解析失败');
        }

        $list = [];
        foreach ((array) $json['list'] as $row) {
            if (is_array($row)) {
                $list[] = $this->normalizeJsonItem($row);
            }
        }
        $types = [];
        foreach ((array) ($json['class'] ?? []) as $ty) {
            if (is_array($ty)) {
                $types[] = [
                    'type_id' => (int) ($ty['type_id'] ?? 0),
                    'type_name' => (string) ($ty['type_name'] ?? ''),
                ];
            }
        }

        return [
            'ok' => true,
            'msg' => 'json',
            'format' => 'json',
            'page' => [
                'page' => (int) ($json['page'] ?? 1),
                'pagecount' => (int) ($json['pagecount'] ?? 1),
                'pagesize' => (int) ($json['limit'] ?? count($list)),
                'recordcount' => (int) ($json['total'] ?? count($list)),
            ],
            'types' => $types,
            'list' => $list,
        ];
    }

    /** @return array<string, mixed> */
    private function parseXml(string $body): array
    {
        $xml = @simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (! $xml) {
            return $this->fail('XML 解析失败');
        }
        $listNode = $xml->list ?? null;
        $attrs = $listNode ? $listNode->attributes() : null;
        $list = [];
        if ($listNode && isset($listNode->video)) {
            foreach ($listNode->video as $video) {
                $list[] = $this->normalizeXmlItem($video);
            }
        }
        $types = [];
        if (isset($xml->class->ty)) {
            foreach ($xml->class->ty as $ty) {
                $types[] = [
                    'type_id' => (int) ($ty->attributes()->id ?? 0),
                    'type_name' => (string) $ty,
                ];
            }
        }

        return [
            'ok' => true,
            'msg' => 'xml',
            'format' => 'xml',
            'page' => [
                'page' => (int) ($attrs->page ?? 1),
                'pagecount' => (int) ($attrs->pagecount ?? 1),
                'pagesize' => (int) ($attrs->pagesize ?? count($list)),
                'recordcount' => (int) ($attrs->recordcount ?? count($list)),
            ],
            'types' => $types,
            'list' => $list,
        ];
    }

    /** @param  array<string, mixed>  $row */
    private function normalizeJsonItem(array $row): array
    {
        return [
            'vod_id' => (string) ($row['vod_id'] ?? ''),
            'type_id' => (int) ($row['type_id'] ?? 0),
            'type_name' => (string) ($row['type_name'] ?? ''),
            'vod_name' => (string) ($row['vod_name'] ?? ''),
            'vod_sub' => (string) ($row['vod_sub'] ?? ''),
            'vod_pic' => (string) ($row['vod_pic'] ?? ''),
            'vod_actor' => (string) ($row['vod_actor'] ?? ''),
            'vod_director' => (string) ($row['vod_director'] ?? ''),
            'vod_area' => (string) ($row['vod_area'] ?? ''),
            'vod_lang' => (string) ($row['vod_lang'] ?? ''),
            'vod_year' => (string) ($row['vod_year'] ?? ''),
            'vod_remarks' => (string) ($row['vod_remarks'] ?? ''),
            'vod_class' => (string) ($row['vod_class'] ?? ($row['type_name'] ?? '')),
            'vod_content' => (string) ($row['vod_content'] ?? ''),
            'vod_serial' => (string) ($row['vod_serial'] ?? ''),
            'vod_weekday' => (string) ($row['vod_weekday'] ?? $row['weekday'] ?? ''),
            'vod_total' => (int) ($row['vod_total'] ?? 0),
            'vod_isend' => (int) ($row['vod_isend'] ?? 0),
            'vod_score' => (float) ($row['vod_score'] ?? 0),
            'vod_play_from' => (string) ($row['vod_play_from'] ?? ''),
            'vod_play_url' => (string) ($row['vod_play_url'] ?? ''),
            'vod_time' => (string) ($row['vod_time'] ?? ''),
        ];
    }

    private function normalizeXmlItem(\SimpleXMLElement $video): array
    {
        $froms = [];
        $urls = [];
        if (isset($video->dl->dd)) {
            foreach ($video->dl->dd as $dd) {
                $froms[] = (string) ($dd['flag'] ?? $dd['from'] ?? '');
                $urls[] = (string) $dd;
            }
        }

        return [
            'vod_id' => (string) ($video->id ?? ''),
            'type_id' => (int) ($video->tid ?? 0),
            'type_name' => (string) ($video->type ?? ''),
            'vod_name' => (string) ($video->name ?? ''),
            'vod_sub' => (string) ($video->subname ?? ''),
            'vod_pic' => (string) ($video->pic ?? ''),
            'vod_actor' => (string) ($video->actor ?? ''),
            'vod_director' => (string) ($video->director ?? ''),
            'vod_area' => (string) ($video->area ?? ''),
            'vod_lang' => (string) ($video->lang ?? ''),
            'vod_year' => (string) ($video->year ?? ''),
            'vod_remarks' => (string) ($video->note ?? ''),
            'vod_class' => (string) ($video->type ?? ''),
            'vod_content' => (string) ($video->des ?? ''),
            'vod_serial' => (string) ($video->state ?? ''),
            'vod_weekday' => (string) ($video->weekday ?? ''),
            'vod_total' => 0,
            'vod_isend' => ((string) ($video->state ?? '') === '' || (string) $video->state === '0') ? 1 : 0,
            'vod_score' => 0,
            'vod_play_from' => implode('$$$', $froms) ?: (string) ($video->dt ?? ''),
            'vod_play_url' => implode('$$$', $urls),
            'vod_time' => (string) ($video->last ?? ''),
        ];
    }

    /** @return array<string, mixed> */
    private function fail(string $msg): array
    {
        return [
            'ok' => false,
            'msg' => $msg,
            'format' => '',
            'page' => ['page' => 1, 'pagecount' => 1, 'pagesize' => 0, 'recordcount' => 0],
            'types' => [],
            'list' => [],
        ];
    }
}

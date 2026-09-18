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
        if ($listNode && isset($listNode->manga)) {
            foreach ($listNode->manga as $manga) {
                $list[] = $this->normalizeXmlItem($manga);
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
        $id = (string) ($row['manga_id'] ?? $row['vod_id'] ?? '');
        $name = (string) ($row['manga_name'] ?? $row['vod_name'] ?? '');
        $pic = (string) ($row['manga_pic'] ?? $row['vod_pic'] ?? '');
        $content = (string) ($row['manga_content'] ?? $row['vod_content'] ?? '');
        $remarks = (string) ($row['manga_remarks'] ?? $row['vod_remarks'] ?? '');
        $playFrom = (string) ($row['manga_play_from'] ?? $row['vod_play_from'] ?? '');
        $playUrl = (string) ($row['manga_play_url'] ?? $row['vod_play_url'] ?? '');

        return [
            'vod_id' => $id,
            'manga_id' => $id,
            'type_id' => (int) ($row['type_id'] ?? 0),
            'type_name' => (string) ($row['type_name'] ?? ''),
            'vod_name' => $name,
            'manga_name' => $name,
            'vod_sub' => (string) ($row['vod_sub'] ?? $row['manga_sub'] ?? ''),
            'vod_pic' => $pic,
            'manga_pic' => $pic,
            'vod_actor' => (string) ($row['vod_actor'] ?? $row['manga_author'] ?? ''),
            'manga_author' => (string) ($row['manga_author'] ?? $row['vod_actor'] ?? ''),
            'vod_director' => (string) ($row['vod_director'] ?? ''),
            'vod_area' => (string) ($row['vod_area'] ?? $row['manga_area'] ?? ''),
            'vod_lang' => (string) ($row['vod_lang'] ?? $row['manga_lang'] ?? ''),
            'vod_year' => (string) ($row['vod_year'] ?? $row['manga_year'] ?? ''),
            'vod_remarks' => $remarks,
            'manga_remarks' => $remarks,
            'vod_class' => (string) ($row['vod_class'] ?? $row['manga_tag'] ?? $row['type_name'] ?? ''),
            'manga_tag' => (string) ($row['manga_tag'] ?? $row['vod_class'] ?? ''),
            'vod_content' => $content,
            'manga_content' => $content,
            'vod_serial' => (string) ($row['vod_serial'] ?? $row['manga_serial'] ?? ''),
            'manga_serial' => (string) ($row['manga_serial'] ?? $row['vod_serial'] ?? ''),
            'vod_weekday' => (string) ($row['vod_weekday'] ?? $row['weekday'] ?? ''),
            'vod_total' => (int) ($row['vod_total'] ?? $row['manga_total'] ?? 0),
            'vod_isend' => (int) ($row['vod_isend'] ?? $row['manga_isend'] ?? 0),
            'manga_isend' => (int) ($row['manga_isend'] ?? $row['vod_isend'] ?? 0),
            'vod_score' => (float) ($row['vod_score'] ?? 0),
            'vod_play_from' => $playFrom,
            'vod_play_url' => $playUrl,
            'manga_play_from' => $playFrom,
            'manga_play_url' => $playUrl,
            'vod_time' => (string) ($row['vod_time'] ?? $row['manga_time'] ?? ''),
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
            'manga_id' => (string) ($video->id ?? ''),
            'type_id' => (int) ($video->tid ?? 0),
            'type_name' => (string) ($video->type ?? ''),
            'vod_name' => (string) ($video->name ?? ''),
            'manga_name' => (string) ($video->name ?? ''),
            'vod_sub' => (string) ($video->subname ?? $video->sub ?? ''),
            'vod_pic' => (string) ($video->pic ?? ''),
            'manga_pic' => (string) ($video->pic ?? ''),
            'vod_actor' => (string) ($video->actor ?? $video->author ?? ''),
            'manga_author' => (string) ($video->author ?? $video->actor ?? ''),
            'vod_director' => (string) ($video->director ?? ''),
            'vod_area' => (string) ($video->area ?? ''),
            'vod_lang' => (string) ($video->lang ?? ''),
            'vod_year' => (string) ($video->year ?? ''),
            'vod_remarks' => (string) ($video->note ?? $video->remarks ?? ''),
            'manga_remarks' => (string) ($video->remarks ?? $video->note ?? ''),
            'vod_class' => (string) ($video->type ?? ''),
            'vod_content' => (string) ($video->des ?? $video->content ?? ''),
            'manga_content' => (string) ($video->content ?? $video->des ?? ''),
            'vod_serial' => (string) ($video->state ?? $video->serial ?? ''),
            'manga_serial' => (string) ($video->serial ?? $video->state ?? ''),
            'vod_weekday' => (string) ($video->weekday ?? ''),
            'vod_total' => 0,
            'vod_isend' => ((string) ($video->state ?? '') === '' || (string) $video->state === '0') ? 1 : 0,
            'vod_score' => 0,
            'vod_play_from' => implode('$$$', $froms) ?: (string) ($video->dt ?? ''),
            'vod_play_url' => implode('$$$', $urls),
            'manga_play_from' => implode('$$$', $froms) ?: (string) ($video->dt ?? ''),
            'manga_play_url' => implode('$$$', $urls),
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

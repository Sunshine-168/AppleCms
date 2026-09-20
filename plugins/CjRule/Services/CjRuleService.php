<?php

namespace Plugins\CjRule\Services;

use App\Models\Video\VideoArt;
use App\Models\Video\VideoCjRule;
use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoStatModel;
use App\Support\Plugins\PluginManager;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugins\CjRule\Models\CjRuleLog;
use Plugins\CjRule\Support\CssSelector;
use Plugins\Manga\Models\Manga;

class CjRuleService
{
    public function ready(): bool
    {
        return Schema::hasTable('video_cj_rules');
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function attributesFromInput(array $input): array
    {
        $type = strtolower(trim((string) ($input['type'] ?? 'html')));
        if (! in_array($type, ['html', 'rss', 'json'], true)) {
            $type = 'html';
        }
        $url = trim((string) ($input['source_url'] ?? $input['url'] ?? ''));
        $status = ! empty($input['status']) && (string) $input['status'] !== '0' ? 1 : 0;
        if (array_key_exists('is_active', $input)) {
            $status = ! empty($input['is_active']) && (string) $input['is_active'] !== '0' ? 1 : 0;
        }

        return [
            'name' => mb_substr(trim((string) ($input['name'] ?? '')) ?: '未命名采集', 0, 80),
            'type' => $type,
            'url' => $url,
            'type_id' => (int) ($input['type_id'] ?? 0),
            'interval_minutes' => max(1, (int) ($input['interval_minutes'] ?? 60)),
            'limit_items' => max(1, min(100, (int) ($input['limit_items'] ?? 10))),
            'status' => $status,
            'publish_immediately' => ! empty($input['publish_immediately']) && (string) $input['publish_immediately'] !== '0' ? 1 : 0,
            'note' => mb_substr(trim((string) ($input['note'] ?? '')), 0, 255),
            'list_rule' => mb_substr(trim((string) ($input['item_selector'] ?? $input['list_rule'] ?? '')), 0, 255),
            'title_rule' => mb_substr(trim((string) ($input['title_selector'] ?? $input['title_rule'] ?? '')), 0, 255),
            'url_rule' => mb_substr(trim((string) ($input['link_selector'] ?? $input['url_rule'] ?? '')), 0, 255),
            'content_rule' => mb_substr(trim((string) ($input['summary_selector'] ?? $input['content_rule'] ?? '')), 0, 255),
            'options' => [
                'into' => $this->normalizeInto($input['into'] ?? 'vod'),
                'list_path' => trim((string) ($input['list_path'] ?? 'items')),
                'title_key' => trim((string) ($input['title_key'] ?? 'title')),
                'link_key' => trim((string) ($input['link_key'] ?? 'url')),
                'summary_key' => trim((string) ($input['summary_key'] ?? 'summary')),
                'content_key' => trim((string) ($input['content_key'] ?? 'content')),
                'guid_key' => trim((string) ($input['guid_key'] ?? 'id')),
                'cover_key' => trim((string) ($input['cover_key'] ?? 'cover')),
                'play_key' => trim((string) ($input['play_key'] ?? 'play')),
                'item_selector' => trim((string) ($input['item_selector'] ?? '')),
                'link_selector' => trim((string) ($input['link_selector'] ?? 'a')),
                'title_selector' => trim((string) ($input['title_selector'] ?? '')),
                'summary_selector' => trim((string) ($input['summary_selector'] ?? '')),
                'cover_selector' => trim((string) ($input['cover_selector'] ?? '')),
                'play_selector' => trim((string) ($input['play_selector'] ?? '')),
                'detail_content_selector' => trim((string) ($input['detail_content_selector'] ?? '')),
                'page_count' => max(1, min(10, (int) ($input['page_count'] ?? 1))),
                'page_url' => trim((string) ($input['page_url'] ?? '')),
                'next_selector' => trim((string) ($input['next_selector'] ?? '')),
            ],
        ];
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function runDue(): array
    {
        if (! $this->ready()) {
            return Result::success(['ran' => 0, 'created' => 0], '还没有规则表');
        }
        $ran = 0;
        $created = 0;
        VideoCjRule::query()->where('status', 1)->orderBy('id')->each(function (VideoCjRule $rule) use (&$ran, &$created) {
            if (! $rule->due()) {
                return;
            }
            $result = $this->importRule($rule);
            $ran++;
            $created += (int) ($result['data']['created'] ?? 0);
        });

        return Result::success(compact('ran', 'created'), '已执行 '.$ran.' 个任务，新建 '.$created.' 条');
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function tryRun(int $id): array
    {
        $rule = VideoCjRule::query()->find($id);
        if (! $rule) {
            return Result::fail('规则不存在');
        }

        return $this->previewRule($rule);
    }

    /**
     * 试抓：不写影片、不记日志。可对未保存的表单字段。
     *
     * @param  array<string, mixed>  $input
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    public function preview(array $input): array
    {
        $id = (int) ($input['id'] ?? 0);
        $hasForm = trim((string) ($input['source_url'] ?? $input['url'] ?? '')) !== ''
            || trim((string) ($input['type'] ?? '')) !== '';
        if ($id > 0 && ! $hasForm) {
            return $this->tryRun($id);
        }
        $rule = new VideoCjRule($this->attributesFromInput($input));

        return $this->previewRule($rule);
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function import(int $id): array
    {
        $rule = VideoCjRule::query()->find($id);
        if (! $rule) {
            return Result::fail('规则不存在');
        }

        return $this->importRule($rule);
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function previewRule(VideoCjRule $rule): array
    {
        try {
            $rule->limit_items = max(1, min(12, (int) ($rule->limit_items ?: 12)));
            $items = $this->fetch($rule, withDetail: false);
            $rows = [];
            foreach ($items as $item) {
                $rows[] = [
                    'title' => $item['title'],
                    'link' => $item['link'],
                    'summary' => Str::limit($item['summary'], 100, ''),
                ];
            }

            return Result::success(
                ['items' => $rows, 'fetched' => count($rows), 'total' => count($rows)],
                admin_t('ui.cj_try_ok', ['n' => count($rows)])
            );
        } catch (\Throwable $e) {
            return Result::fail($e->getMessage() !== '' ? $e->getMessage() : admin_t('ui.try_fetch_fail'), ['items' => []]);
        }
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function importRule(VideoCjRule $rule): array
    {
        $into = $this->into($rule);
        if ($into === 'art' && ! Schema::hasTable('video_arts')) {
            return Result::fail('请先执行数据库迁移');
        }
        if ($into === 'manga' && ! $this->mangaReady()) {
            return Result::fail('漫画插件未启用，不能写入漫画库');
        }
        if ($into === 'vod' && ! Schema::hasTable('videos')) {
            return Result::fail('影片表不存在');
        }
        try {
            $items = $this->fetch($rule, withDetail: true);
            $fetched = count($items);
            $created = 0;
            $skipped = 0;
            $limit = max(1, min(100, (int) ($rule->limit_items ?: 10)));
            foreach (array_slice($items, 0, $limit) as $item) {
                if ($this->upsert($rule, $item)) {
                    $created++;
                } else {
                    $skipped++;
                }
            }
            $message = '拉取 '.$fetched.'，新建 '.$created.'，跳过 '.$skipped;
            $this->finish($rule, 'ok', $message, $fetched, $created, $skipped);

            return Result::success(compact('fetched', 'created', 'skipped'), $message);
        } catch (\Throwable $e) {
            $this->finish($rule, 'fail', $e->getMessage(), 0, 0, 0);

            return Result::fail('采集失败：'.$e->getMessage());
        }
    }

    /**
     * @return list<array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}>
     */
    public function fetch(VideoCjRule $rule, bool $withDetail = true): array
    {
        return match ($this->resolveType($rule)) {
            'json' => $this->fetchJson($rule),
            'rss' => $this->fetchRss($rule),
            'regex' => $this->fetchRegex($rule),
            default => $this->fetchHtml($rule, $withDetail),
        };
    }

    public function resolveType(VideoCjRule $rule): string
    {
        $type = strtolower(trim((string) ($rule->type ?? '')));
        $opts = is_array($rule->options) ? $rule->options : [];
        $itemSel = trim((string) ($opts['item_selector'] ?? ''));
        $listRule = trim((string) ($rule->list_rule ?? ''));
        if ($type === 'regex' || ($itemSel === '' && $listRule !== '' && ! in_array($type, ['rss', 'json'], true) && str_contains($listRule, '('))) {
            return 'regex';
        }
        if (in_array($type, ['html', 'rss', 'json'], true)) {
            return $type;
        }
        if ($itemSel !== '') {
            return 'html';
        }
        if ($listRule !== '') {
            return 'regex';
        }

        return 'html';
    }

    /**
     * @return list<array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}>
     */
    protected function fetchRss(VideoCjRule $rule): array
    {
        $url = $this->sourceUrl($rule);
        $this->assertPublicHttpUrl($url);
        $response = Http::timeout(25)
            ->withHeaders(['User-Agent' => 'LaraVideo-Collector/1.0'])
            ->get($url);
        if (! $response->successful()) {
            throw new \RuntimeException('HTTP '.$response->status());
        }
        $xml = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
        if (! $xml) {
            throw new \RuntimeException('无法解析 RSS/Atom XML');
        }
        $items = [];
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $node) {
                $link = trim((string) ($node->link ?? ''));
                $guid = trim((string) ($node->guid ?? $link));
                $title = trim((string) ($node->title ?? '无标题'));
                $summary = trim(strip_tags((string) ($node->description ?? '')));
                $items[] = $this->item(
                    $guid ?: md5($link.$title),
                    $title,
                    $link,
                    $summary,
                    trim((string) ($node->children('content', true)->encoded ?? $node->description ?? '')),
                    $this->guessCover((string) ($node->description ?? ''))
                );
            }
        }
        if ($items === [] && isset($xml->entry)) {
            foreach ($xml->entry as $node) {
                $link = '';
                foreach ($node->link ?? [] as $l) {
                    $href = (string) ($l['href'] ?? '');
                    if ($href && ((string) ($l['rel'] ?? 'alternate') === 'alternate' || $link === '')) {
                        $link = $href;
                    }
                }
                $id = trim((string) ($node->id ?? $link));
                $title = trim((string) ($node->title ?? '无标题'));
                $summary = trim(strip_tags((string) ($node->summary ?? '')));
                $items[] = $this->item(
                    $id ?: md5($link.$title),
                    $title,
                    $link,
                    $summary,
                    trim((string) ($node->content ?? $node->summary ?? '')),
                    null
                );
            }
        }
        if ($items === []) {
            throw new \RuntimeException('订阅里没有条目');
        }

        return $items;
    }

    /**
     * @return list<array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}>
     */
    protected function fetchJson(VideoCjRule $rule): array
    {
        $url = $this->sourceUrl($rule);
        $this->assertPublicHttpUrl($url);
        $response = Http::timeout(25)
            ->withHeaders(['User-Agent' => 'LaraVideo-Collector/1.0', 'Accept' => 'application/json'])
            ->get($url);
        if (! $response->successful()) {
            throw new \RuntimeException('HTTP '.$response->status());
        }
        $json = $response->json();
        $opts = is_array($rule->options) ? $rule->options : [];
        $listPath = (string) ($opts['list_path'] ?? 'items');
        $list = $listPath === '' || $listPath === '.' ? $json : data_get($json, $listPath, []);
        if (! is_array($list)) {
            throw new \RuntimeException('JSON 列表路径无效：'.$listPath);
        }
        $map = [
            'title' => $opts['title_key'] ?? 'title',
            'link' => $opts['link_key'] ?? 'url',
            'summary' => $opts['summary_key'] ?? 'summary',
            'content' => $opts['content_key'] ?? 'content',
            'guid' => $opts['guid_key'] ?? 'id',
            'cover' => $opts['cover_key'] ?? 'cover',
            'play' => $opts['play_key'] ?? 'play',
        ];
        $items = [];
        foreach ($list as $row) {
            if (! is_array($row)) {
                continue;
            }
            $title = (string) data_get($row, $map['title'], '');
            $link = (string) data_get($row, $map['link'], '');
            $guid = (string) (data_get($row, $map['guid']) ?: $link ?: md5($title));
            if ($title === '') {
                continue;
            }
            $items[] = $this->item(
                $guid,
                $title,
                $link,
                (string) data_get($row, $map['summary'], ''),
                (string) data_get($row, $map['content'], data_get($row, $map['summary'], '')),
                data_get($row, $map['cover']) ? (string) data_get($row, $map['cover']) : null,
                (string) data_get($row, $map['play'], '')
            );
        }
        if ($items === []) {
            throw new \RuntimeException('接口列表里没有标题');
        }

        return $items;
    }

    /**
     * @return list<array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}>
     */
    protected function fetchHtml(VideoCjRule $rule, bool $withDetail = true): array
    {
        $source = $this->sourceUrl($rule);
        $this->assertPublicHttpUrl($source);
        $opts = is_array($rule->options) ? $rule->options : [];
        $itemSel = trim((string) ($opts['item_selector'] ?? ''));
        if ($itemSel === '') {
            throw new \RuntimeException('请填写列表条目选择器');
        }
        $max = max(1, min(100, (int) ($rule->limit_items ?: 10)));
        $pageCount = max(1, min(10, (int) ($opts['page_count'] ?? 1)));
        $pageUrl = trim((string) ($opts['page_url'] ?? ''));
        $nextSel = trim((string) ($opts['next_selector'] ?? ''));
        $baseHost = $this->hostOf($source);
        $items = [];
        $seenUrls = [];
        $seenGuids = [];
        $url = $source;
        for ($page = 1; $page <= $pageCount && count($items) < $max; $page++) {
            if ($nextSel === '' && $page > 1) {
                $url = $this->htmlPageUrl($source, $pageUrl, $page);
            }
            if ($url === '' || isset($seenUrls[$url])) {
                break;
            }
            $this->assertPublicHttpUrl($url);
            if ($this->hostOf($url) !== $baseHost) {
                if ($page === 1) {
                    throw new \RuntimeException('翻页地址必须与列表页同站');
                }
                break;
            }
            $seenUrls[$url] = true;
            try {
                $html = $this->httpGet($url);
            } catch (\Throwable $e) {
                if ($page === 1) {
                    throw $e;
                }
                break;
            }
            $xpath = $this->htmlXPath($html);
            $nodes = $xpath->query(CssSelector::toXPath($itemSel, false));
            if ($nodes === false || $nodes->length === 0) {
                if ($page === 1) {
                    throw new \RuntimeException('这一页没有匹配到列表条目，请检查选择器');
                }
                break;
            }
            foreach ($this->parseHtmlListItems($rule, $xpath, $nodes, $url, $baseHost, $max - count($items), $withDetail) as $item) {
                if (isset($seenGuids[$item['guid']])) {
                    continue;
                }
                $seenGuids[$item['guid']] = true;
                $items[] = $item;
                if (count($items) >= $max) {
                    break;
                }
            }
            if ($nextSel !== '' && $page < $pageCount && count($items) < $max) {
                $nextHref = $this->docAttr($xpath, $nextSel, 'href');
                $next = $nextHref ? $this->absoluteUrl($url, $nextHref) : '';
                if ($next === '' || $this->hostOf($next) !== $baseHost) {
                    break;
                }
                $url = $next;
            }
        }

        return $items;
    }

    /**
     * @param  \DOMNodeList<\DOMNode>  $nodes
     * @return list<array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}>
     */
    protected function parseHtmlListItems(
        VideoCjRule $rule,
        \DOMXPath $xpath,
        \DOMNodeList $nodes,
        string $listUrl,
        string $baseHost,
        int $remain,
        bool $withDetail,
    ): array {
        $opts = is_array($rule->options) ? $rule->options : [];
        $linkSel = trim((string) ($opts['link_selector'] ?? 'a'));
        $titleSel = trim((string) ($opts['title_selector'] ?? ''));
        $summarySel = trim((string) ($opts['summary_selector'] ?? ''));
        $coverSel = trim((string) ($opts['cover_selector'] ?? ''));
        $playSel = trim((string) ($opts['play_selector'] ?? ''));
        $detailSel = trim((string) ($opts['detail_content_selector'] ?? ''));
        $items = [];
        foreach ($nodes as $node) {
            if (count($items) >= $remain) {
                break;
            }
            if (! $node instanceof \DOMNode) {
                continue;
            }
            $href = $this->firstAttr($xpath, $node, $linkSel !== '' ? $linkSel : 'a', 'href');
            $link = $href ? $this->absoluteUrl($listUrl, $href) : '';
            $title = $titleSel !== ''
                ? $this->firstText($xpath, $node, $titleSel)
                : $this->firstText($xpath, $node, $linkSel !== '' ? $linkSel : 'a');
            if ($title === '' && $node instanceof \DOMElement) {
                $title = trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? '');
            }
            $title = Str::limit($title, 180, '');
            if ($title === '') {
                continue;
            }
            $summary = $summarySel !== '' ? $this->firstText($xpath, $node, $summarySel) : '';
            $coverHref = $coverSel !== '' ? $this->firstAttr($xpath, $node, $coverSel, 'src') : null;
            $cover = $coverHref
                ? $this->absoluteUrl($listUrl, $coverHref)
                : $this->guessCover($node instanceof \DOMElement ? ($node->ownerDocument?->saveHTML($node) ?: '') : '');
            $play = '';
            if ($playSel !== '') {
                $playHref = $this->firstAttr($xpath, $node, $playSel, 'href')
                    ?: $this->firstAttr($xpath, $node, $playSel, 'src')
                    ?: $this->firstText($xpath, $node, $playSel);
                $play = $playHref ? $this->absoluteUrl($listUrl, $playHref) : '';
            }
            $content = $summary;
            if ($withDetail && $detailSel !== '' && $link !== '' && $this->hostOf($link) === $baseHost) {
                try {
                    $this->assertPublicHttpUrl($link);
                    $detail = $this->httpGet($link, 15);
                    $dx = $this->htmlXPath($detail);
                    $contentNode = $dx->query(CssSelector::toXPath($detailSel, false));
                    if ($contentNode && $contentNode->length > 0) {
                        $content = $this->innerHtml($contentNode->item(0));
                    }
                } catch (\Throwable) {
                }
            }
            $items[] = $this->item(
                $link !== '' ? $link : md5($title),
                $title,
                $link,
                $summary,
                $content,
                $cover,
                $play
            );
        }

        return $items;
    }

    /**
     * @return list<array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}>
     */
    protected function fetchRegex(VideoCjRule $rule): array
    {
        $url = $this->sourceUrl($rule);
        $this->assertPublicHttpUrl($url);
        $html = $this->httpGet($url, 15);
        $chunks = $this->matchAll((string) $rule->list_rule, $html);
        if ($chunks === [] && trim((string) $rule->list_rule) === '') {
            $chunks = [$html];
        }
        if ($chunks === []) {
            throw new \RuntimeException('列表规则没有匹配到内容');
        }
        $items = [];
        foreach (array_slice($chunks, 0, 50) as $chunk) {
            $title = $this->first((string) $rule->title_rule, $chunk);
            $link = $this->first((string) $rule->url_rule, $chunk);
            $content = $this->first((string) ($rule->content_rule ?? ''), $chunk);
            if ($title === '' && $link === '') {
                continue;
            }
            if ($link !== '' && ! preg_match('#^https?://#i', $link)) {
                $link = $this->absoluteUrl($url, $link);
            }
            $title = html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $content = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $items[] = $this->item($link !== '' ? $link : md5($title), $title, $link, $content, $content, null, $link);
        }
        if ($items === []) {
            throw new \RuntimeException('标题/地址规则没有匹配到内容');
        }

        return $items;
    }

    /**
     * @param  array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}  $item
     */
    protected function upsert(VideoCjRule $rule, array $item): bool
    {
        return match ($this->into($rule)) {
            'art' => $this->upsertArt($rule, $item),
            'manga' => $this->upsertManga($rule, $item),
            default => $this->upsertVideo($rule, $item),
        };
    }

    protected function upsertVideo(VideoCjRule $rule, array $item): bool
    {
        $title = trim($item['title']);
        if ($title === '') {
            return false;
        }
        $guid = $this->collectKey($rule, $item['guid']);
        if (Schema::hasColumn('videos', 'collect_id') && $guid !== '') {
            $exists = VideoModel::query()->where('collect_id', $guid)->first();
            if ($exists) {
                return false;
            }
        }
        if (VideoModel::query()->where('title', $title)->exists()) {
            return false;
        }
        $now = time();
        $play = $this->pickPlay($item);
        $publish = (int) ($rule->publish_immediately ?? 0) === 1 && $play !== '';
        $body = trim(strip_tags((string) $item['content'], '<p><br><a><img><ul><ol><li><strong><em><h2><h3>'));
        if ($body === '') {
            $body = trim($item['summary']);
        }
        if ($item['link'] !== '' && ! str_contains($body, $item['link'])) {
            $body = trim($body."\n\n来源：".$item['link']);
        }
        $video = new VideoModel();
        $payload = [
            'title' => mb_substr($title, 0, 255),
            'type_id' => (int) ($rule->type_id ?? 0) ?: null,
            'description' => $body !== '' ? $body : null,
            'status' => $publish ? 1 : 0,
            'remarks' => mb_substr('网站采集', 0, 100),
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if (Schema::hasColumn('videos', 'cover') && ! empty($item['cover'])) {
            $payload['cover'] = mb_substr((string) $item['cover'], 0, 1024);
        }
        if (Schema::hasColumn('videos', 'collect_id')) {
            $payload['collect_id'] = $guid;
        }
        $video->fill($payload);
        $video->save();
        if (Schema::hasTable('video_stats')) {
            VideoStatModel::query()->create([
                'video_id' => $video->id,
                'hits' => 0,
                'hits_day' => 0,
                'hits_week' => 0,
                'hits_month' => 0,
                'up' => 0,
                'down' => 0,
                'score' => 0,
                'score_all' => 0,
                'score_num' => 0,
                'updated_at' => $now,
            ]);
        }
        if ($play !== '' && Schema::hasTable('video_sources') && Schema::hasTable('video_episodes')) {
            $source = new VideoSourceModel();
            $source->fill([
                'video_id' => $video->id,
                'name' => '网站采集',
                'type' => 'play',
                'status' => 1,
                'sort' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $source->save();
            VideoEpisodeModel::query()->create([
                'video_id' => $video->id,
                'source_id' => $source->id,
                'episode_name' => '1',
                'episode_num' => 1,
                'url' => mb_substr($play, 0, 2000),
                'status' => 1,
                'sort' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return true;
    }

    /**
     * @param  array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}  $item
     */
    protected function upsertArt(VideoCjRule $rule, array $item): bool
    {
        $title = trim($item['title']);
        if ($title === '' || ! Schema::hasTable('video_arts')) {
            return false;
        }
        $guid = $this->collectKey($rule, $item['guid']);
        if (Schema::hasColumn('video_arts', 'source') && $guid !== '') {
            if (VideoArt::query()->where('source', $guid)->exists()) {
                return false;
            }
        }
        if (VideoArt::query()->where('title', $title)->exists()) {
            return false;
        }
        $now = time();
        $body = $this->itemBody($item);
        $publish = (int) ($rule->publish_immediately ?? 0) === 1;
        $payload = [
            'title' => mb_substr($title, 0, 200),
            'type_id' => (int) ($rule->type_id ?? 0),
            'content' => $body !== '' ? $body : null,
            'status' => $publish ? 1 : 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if (Schema::hasColumn('video_arts', 'cover') && ! empty($item['cover'])) {
            $payload['cover'] = mb_substr((string) $item['cover'], 0, 255);
        }
        if (Schema::hasColumn('video_arts', 'source') && $guid !== '') {
            $payload['source'] = mb_substr($guid, 0, 120);
        }
        if (Schema::hasColumn('video_arts', 'blurb')) {
            $payload['blurb'] = mb_substr(trim($item['summary']), 0, 500);
        }
        if (Schema::hasColumn('video_arts', 'published_at')) {
            $payload['published_at'] = $publish ? $now : 0;
        }
        $art = new VideoArt();
        $art->fill($payload);
        $art->save();

        return true;
    }

    /**
     * @param  array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}  $item
     */
    protected function upsertManga(VideoCjRule $rule, array $item): bool
    {
        $title = trim($item['title']);
        if ($title === '' || ! $this->mangaReady()) {
            return false;
        }
        $guid = $this->collectKey($rule, $item['guid']);
        if (Schema::hasColumn('plugin_mangas', 'remarks') && $guid !== '') {
            if (Manga::query()->where('remarks', $guid)->exists()) {
                return false;
            }
        }
        if (Manga::query()->where('title', $title)->exists()) {
            return false;
        }
        $now = time();
        $body = $this->itemBody($item);
        $publish = (int) ($rule->publish_immediately ?? 0) === 1;
        $payload = [
            'title' => mb_substr($title, 0, 200),
            'content' => $body !== '' ? $body : null,
            'status' => $publish ? 1 : 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if (Schema::hasColumn('plugin_mangas', 'type_id')) {
            $payload['type_id'] = (int) ($rule->type_id ?? 0);
        }
        if (Schema::hasColumn('plugin_mangas', 'cover') && ! empty($item['cover'])) {
            $payload['cover'] = mb_substr((string) $item['cover'], 0, 500);
        }
        if (Schema::hasColumn('plugin_mangas', 'remarks') && $guid !== '') {
            $payload['remarks'] = mb_substr($guid, 0, 80);
        }
        if (Schema::hasColumn('plugin_mangas', 'yid')) {
            $payload['yid'] = 0;
        }
        $row = new Manga();
        $row->fill($payload);
        $row->save();

        return true;
    }

    /**
     * @param  array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}  $item
     */
    protected function itemBody(array $item): string
    {
        $body = trim(strip_tags((string) $item['content'], '<p><br><a><img><ul><ol><li><strong><em><h2><h3>'));
        if ($body === '') {
            $body = trim($item['summary']);
        }
        if ($item['link'] !== '' && ! str_contains($body, $item['link'])) {
            $body = trim($body."\n\n来源：".$item['link']);
        }

        return $body;
    }

    public function into(VideoCjRule $rule): string
    {
        $opts = is_array($rule->options) ? $rule->options : [];

        return $this->normalizeInto($opts['into'] ?? 'vod');
    }

    public function mangaReady(): bool
    {
        try {
            return app(PluginManager::class)->isEnabled('manga') && Schema::hasTable('plugin_mangas');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function intoLabel(string $into): string
    {
        return match ($into) {
            'art' => admin_t('ui.articles'),
            'manga' => admin_t('ui.chip_manga'),
            default => admin_t('ui.chip_videos'),
        };
    }

    private function normalizeInto(mixed $raw): string
    {
        $into = strtolower(trim((string) $raw));

        return in_array($into, ['vod', 'art', 'manga'], true) ? $into : 'vod';
    }

    /**
     * @param  array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}  $item
     */
    protected function pickPlay(array $item): string
    {
        foreach ([$item['play'], $item['link']] as $url) {
            $url = trim((string) $url);
            if ($this->looksLikePlay($url)) {
                return $url;
            }
        }

        return '';
    }

    protected function looksLikePlay(string $url): bool
    {
        if ($url === '' || ! preg_match('#^https?://#i', $url)) {
            return false;
        }

        return (bool) preg_match('/\.(m3u8|mp4|flv|mkv|webm)(\?|$)/i', $url);
    }

    protected function collectKey(VideoCjRule $rule, string $guid): string
    {
        $id = (int) ($rule->id ?? 0);

        return mb_substr('cj'.$id.':'.sha1($guid), 0, 64);
    }

    protected function finish(
        VideoCjRule $rule,
        string $status,
        string $message,
        int $fetched,
        int $created,
        int $skipped,
    ): void {
        if (! $rule->exists) {
            return;
        }
        $rule->forceFill([
            'last_run_at' => time(),
            'last_status' => $status,
            'last_message' => Str::limit($message, 500, ''),
        ])->save();
        if (! Schema::hasTable('video_cj_rule_logs')) {
            return;
        }
        CjRuleLog::query()->create([
            'rule_id' => (int) $rule->id,
            'status' => $status,
            'fetched' => $fetched,
            'created' => $created,
            'skipped' => $skipped,
            'message' => Str::limit($message, 1000, ''),
            'created_at' => time(),
        ]);
    }

    /**
     * @return array{guid:string,title:string,link:string,summary:string,content:string,cover:?string,play:string}
     */
    protected function item(
        string $guid,
        string $title,
        string $link,
        string $summary,
        string $content,
        ?string $cover,
        string $play = '',
    ): array {
        return [
            'guid' => $guid,
            'title' => $title !== '' ? $title : '无标题',
            'link' => $link,
            'summary' => $summary,
            'content' => $content,
            'cover' => $cover,
            'play' => $play,
        ];
    }

    protected function sourceUrl(VideoCjRule $rule): string
    {
        return trim((string) $rule->url);
    }

    protected function htmlPageUrl(string $source, string $template, int $page): string
    {
        if ($page <= 1) {
            return $source;
        }
        if ($template !== '') {
            if (! str_contains($template, '{page}')) {
                throw new \RuntimeException('翻页地址请包含 {page}，例如 https://example.com/list?page={page}');
            }

            return str_replace('{page}', (string) $page, $template);
        }
        $parts = parse_url($source) ?: [];
        parse_str($parts['query'] ?? '', $query);
        $query['page'] = $page;
        $qs = http_build_query($query);
        $origin = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '');
        if (! empty($parts['port'])) {
            $origin .= ':'.$parts['port'];
        }
        $path = $parts['path'] ?? '/';

        return $origin.$path.($qs !== '' ? '?'.$qs : '');
    }

    protected function httpGet(string $url, int $timeout = 25): string
    {
        $response = Http::timeout($timeout)
            ->withHeaders(['User-Agent' => 'LaraVideo-Collector/1.0'])
            ->get($url);
        if (! $response->successful()) {
            throw new \RuntimeException('HTTP '.$response->status().' '.$url);
        }
        $body = $response->body();
        if (strlen($body) > 1_500_000) {
            $body = substr($body, 0, 1_500_000);
        }

        return $body;
    }

    protected function htmlXPath(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return new \DOMXPath($dom);
    }

    protected function firstText(\DOMXPath $xpath, \DOMNode $context, string $selector): string
    {
        $nodes = $xpath->query(CssSelector::toXPath($selector, true), $context);
        if (! $nodes || $nodes->length === 0) {
            return '';
        }

        return trim(preg_replace('/\s+/u', ' ', $nodes->item(0)?->textContent ?? '') ?? '');
    }

    protected function firstAttr(\DOMXPath $xpath, \DOMNode $context, string $selector, string $attr): ?string
    {
        $nodes = $xpath->query(CssSelector::toXPath($selector, true), $context);
        if (! $nodes || $nodes->length === 0) {
            return null;
        }
        $node = $nodes->item(0);
        if ($node instanceof \DOMElement && $node->hasAttribute($attr)) {
            return trim($node->getAttribute($attr));
        }
        if ($context instanceof \DOMElement && strtolower($context->nodeName) === strtolower($selector) && $context->hasAttribute($attr)) {
            return trim($context->getAttribute($attr));
        }

        return null;
    }

    protected function docAttr(\DOMXPath $xpath, string $selector, string $attr): ?string
    {
        $nodes = $xpath->query(CssSelector::toXPath($selector, false));
        if (! $nodes || $nodes->length === 0) {
            return null;
        }
        $node = $nodes->item(0);
        if ($node instanceof \DOMElement && $node->hasAttribute($attr)) {
            return trim($node->getAttribute($attr));
        }

        return null;
    }

    protected function innerHtml(?\DOMNode $node): string
    {
        if (! $node || ! $node->ownerDocument) {
            return '';
        }
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return trim($html);
    }

    protected function absoluteUrl(string $base, string $href): string
    {
        $href = trim($href);
        if ($href === '' || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:')) {
            return '';
        }
        if (preg_match('#^https?://#i', $href)) {
            return $href;
        }
        $parts = parse_url($base);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $origin = $scheme.'://'.$host.$port;
        if (str_starts_with($href, '//')) {
            return $scheme.':'.$href;
        }
        if (str_starts_with($href, '/')) {
            return $origin.$href;
        }
        $dir = rtrim(str_replace('\\', '/', dirname($parts['path'] ?? '/')), '/');

        return $origin.($dir === '' ? '' : $dir).'/'.$href;
    }

    protected function hostOf(string $url): string
    {
        return strtolower((string) parse_url($url, PHP_URL_HOST));
    }

    protected function assertPublicHttpUrl(string $url): void
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new \RuntimeException('仅允许 http/https 源地址');
        }
        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || $host === 'localhost' || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            throw new \RuntimeException('不允许内网地址');
        }
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ok = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
            if ($ok === false) {
                throw new \RuntimeException('不允许内网地址');
            }
        }
    }

    protected function guessCover(string $html): ?string
    {
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
            return $m[1];
        }

        return null;
    }

    /** @return list<string> */
    private function matchAll(string $pattern, string $html): array
    {
        $rx = $this->compile($pattern);
        if ($rx === '') {
            return [];
        }
        $ok = @preg_match_all($rx, $html, $m);
        if ($ok === false || $ok === 0) {
            return [];
        }
        if (! empty($m[1])) {
            return array_values(array_map('strval', $m[1]));
        }

        return array_values(array_map('strval', $m[0] ?? []));
    }

    private function first(string $pattern, string $html): string
    {
        $rx = $this->compile($pattern);
        if ($rx === '') {
            return '';
        }
        $ok = @preg_match($rx, $html, $m);
        if ($ok !== 1) {
            return '';
        }

        return (string) ($m[1] ?? $m[0] ?? '');
    }

    private function compile(string $pattern): string
    {
        $pattern = trim($pattern);
        if ($pattern === '') {
            return '';
        }
        if ($pattern[0] === '/' || $pattern[0] === '#' || $pattern[0] === '~') {
            return $pattern;
        }

        return '#'.$pattern.'#su';
    }
}

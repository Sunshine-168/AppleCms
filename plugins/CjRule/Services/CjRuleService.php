<?php

namespace Plugins\CjRule\Services;

use App\Models\Video\VideoCjRule;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoStatModel;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class CjRuleService
{
    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function tryRun(int $id): array
    {
        $parsed = $this->fetchItems($id);
        if (($parsed['code'] ?? 1) !== 0) {
            return $parsed;
        }
        $items = array_slice($parsed['data']['items'] ?? [], 0, 10);

        return Result::success(['items' => $items, 'total' => count($parsed['data']['items'] ?? [])], '试跑完成');
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function import(int $id): array
    {
        $parsed = $this->fetchItems($id);
        if (($parsed['code'] ?? 1) !== 0) {
            return $parsed;
        }
        if (! Schema::hasTable('videos')) {
            return Result::fail('影片表不存在');
        }
        $created = 0;
        $skipped = 0;
        $now = time();
        foreach ($parsed['data']['items'] ?? [] as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                $skipped++;
                continue;
            }
            $exists = VideoModel::query()->where('title', $title)->first();
            if ($exists) {
                $skipped++;
                continue;
            }
            $video = new VideoModel();
            $video->fill([
                'title' => mb_substr($title, 0, 255),
                'type_id' => (int) ($parsed['data']['type_id'] ?? 0) ?: null,
                'description' => mb_substr((string) ($item['content'] ?? ''), 0, 2000),
                'status' => 2,
                'remarks' => '规则采集',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
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
            $play = trim((string) ($item['url'] ?? ''));
            if ($play !== '' && Schema::hasTable('video_sources') && Schema::hasTable('video_episodes')) {
                $source = new \App\Models\Video\VideoSourceModel();
                $source->fill([
                    'video_id' => $video->id,
                    'name' => '规则采集',
                    'type' => 'play',
                    'status' => 1,
                    'sort' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $source->save();
                \App\Models\Video\VideoEpisodeModel::query()->create([
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
                $video->status = 1;
                $video->save();
            }
            $created++;
        }

        return Result::success(['created' => $created, 'skipped' => $skipped], '入库 '.$created.'，跳过 '.$skipped);
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function fetchItems(int $id): array
    {
        $rule = VideoCjRule::query()->find($id);
        if (! $rule) {
            return Result::fail('规则不存在');
        }
        $url = trim((string) $rule->url);
        if ($url === '' || ! preg_match('#^https?://#i', $url)) {
            return Result::fail('列表地址必须是 http/https');
        }
        try {
            $res = Http::timeout(15)->withHeaders(['User-Agent' => 'LaraVideo-CjRule/1.0'])->get($url);
        } catch (\Throwable $e) {
            return Result::fail($e->getMessage() !== '' ? $e->getMessage() : '抓取失败');
        }
        if (! $res->successful()) {
            return Result::fail('抓取失败 HTTP '.$res->status());
        }
        $html = $res->body();
        if (strlen($html) > 2_000_000) {
            $html = substr($html, 0, 2_000_000);
        }
        $chunks = $this->matchAll((string) $rule->list_rule, $html);
        if ($chunks === [] && trim((string) $rule->list_rule) === '') {
            $chunks = [$html];
        }
        if ($chunks === []) {
            return Result::fail('列表规则没有匹配到内容');
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
                $link = $this->joinUrl($url, $link);
            }
            $items[] = [
                'title' => html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'url' => $link,
                'content' => html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            ];
        }
        if ($items === []) {
            return Result::fail('标题/地址规则没有匹配到内容');
        }

        return Result::success(['items' => $items, 'type_id' => (int) ($rule->type_id ?? 0)]);
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

    private function joinUrl(string $base, string $rel): string
    {
        $rel = trim($rel);
        if ($rel === '') {
            return '';
        }
        if (str_starts_with($rel, '//')) {
            return 'https:'.$rel;
        }
        $parts = parse_url($base);
        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return $rel;
        }
        $origin = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
        if (str_starts_with($rel, '/')) {
            return $origin.$rel;
        }
        $path = (string) ($parts['path'] ?? '/');
        $dir = preg_replace('#/[^/]*$#', '/', $path) ?: '/';

        return $origin.$dir.$rel;
    }
}

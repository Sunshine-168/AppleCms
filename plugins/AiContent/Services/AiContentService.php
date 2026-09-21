<?php

namespace Plugins\AiContent\Services;

use App\Models\Video\VideoModel;
use App\Services\Video\VideoSettingService;
use App\Support\HttpSsl;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class AiContentService
{
    public const BATCH_MAX = 8;

    public function __construct(private readonly VideoSettingService $settings) {}

    public function ready(): bool
    {
        return $this->opt('ai_key') !== '';
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    public function generate(string $title, string $hint = ''): array
    {
        $title = trim($title);
        if ($title === '') {
            return Result::fail('请填写标题');
        }
        $prompt = '为影视作品《'.$title.'》写一段不超过120字的中文剧情简介，不要标题、不要剧透结局、不要列表。';
        if (trim($hint) !== '') {
            $prompt .= '已有资料：'.mb_substr(trim($hint), 0, 400);
        }
        $chat = $this->chat(
            '你是影视资料编辑，只输出简介正文。',
            $prompt,
            400
        );
        if ((int) ($chat['code'] ?? 1) !== 0) {
            return $chat;
        }

        return Result::success(['text' => (string) ($chat['data']['text'] ?? '')], '已生成');
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    public function generateSeo(array $ctx): array
    {
        $title = trim((string) ($ctx['title'] ?? ''));
        if ($title === '') {
            return Result::fail('请填写标题');
        }
        $site = trim((string) $this->settings->get('site_title', ''));
        $bits = ['片名：'.$title];
        foreach (['type' => '分类', 'year' => '年份', 'area' => '地区', 'actors' => '主演'] as $key => $label) {
            $val = trim((string) ($ctx[$key] ?? ''));
            if ($val !== '') {
                $bits[] = $label.'：'.mb_substr($val, 0, 80);
            }
        }
        $hint = trim((string) ($ctx['hint'] ?? ''));
        if ($hint !== '') {
            $bits[] = '简介：'.mb_substr($hint, 0, 400);
        }
        if ($site !== '') {
            $bits[] = '站点：'.$site;
        }
        $prompt = "根据资料写影视详情页 SEO。只输出 JSON：{\"title\":\"\",\"keywords\":\"\",\"description\":\"\"}。\n"
            ."title 不超过40字，含片名，可带在线观看之类检索词。\n"
            ."keywords 用逗号分隔 5 到 10 个词。\n"
            ."description 不超过80字，不剧透结局。\n"
            .implode("\n", $bits);
        $chat = $this->chat(
            '你是影视站 SEO 编辑。只输出一个 JSON 对象，不要 markdown。',
            $prompt,
            500
        );
        if ((int) ($chat['code'] ?? 1) !== 0) {
            return $chat;
        }
        $parsed = $this->parseSeoJson((string) ($chat['data']['text'] ?? ''));
        if ($parsed === null) {
            return Result::fail('模型没有返回可用的 SEO');
        }

        return Result::success($parsed, '已生成');
    }

    /**
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    public function applySeo(int $id, bool $overwrite = false): array
    {
        if ($id < 1) {
            return Result::fail('影片不存在');
        }
        if (! Schema::hasColumn('videos', 'seo_title')) {
            return Result::fail('请先执行数据库迁移');
        }
        $video = VideoModel::query()->find($id);
        if (! $video) {
            return Result::fail('影片不存在');
        }
        if (! $overwrite && $this->seoFilled($video)) {
            return Result::success([
                'id' => $id,
                'skip' => true,
                'title' => (string) $video->seo_title,
                'keywords' => (string) $video->seo_keywords,
                'description' => (string) $video->seo_description,
            ], '已有 SEO，已跳过');
        }
        $gen = $this->generateSeo($this->videoCtx($video));
        if ((int) ($gen['code'] ?? 1) !== 0) {
            return $gen;
        }
        $row = $gen['data'] ?? [];
        $video->seo_title = (string) ($row['title'] ?? '');
        $video->seo_keywords = (string) ($row['keywords'] ?? '');
        $video->seo_description = (string) ($row['description'] ?? '');
        $video->updated_at = time();
        $video->save();

        return Result::success([
            'id' => $id,
            'skip' => false,
            'title' => $video->seo_title,
            'keywords' => $video->seo_keywords,
            'description' => $video->seo_description,
        ], '已写入 SEO');
    }

    /**
     * @param  list<int>  $ids
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    public function applySeoBatch(array $ids, bool $overwrite = false): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
        if ($ids === []) {
            return Result::fail('请先勾选影片');
        }
        if (count($ids) > self::BATCH_MAX) {
            return Result::fail('一次最多 '.self::BATCH_MAX.' 部');
        }
        $done = 0;
        $skip = 0;
        $fail = 0;
        $errors = [];
        foreach ($ids as $id) {
            $res = $this->applySeo($id, $overwrite);
            if ((int) ($res['code'] ?? 1) !== 0) {
                $fail++;
                $errors[] = '#'.$id.' '.((string) ($res['msg'] ?? '失败'));
                continue;
            }
            if (! empty($res['data']['skip'])) {
                $skip++;
            } else {
                $done++;
            }
        }

        return Result::success([
            'done' => $done,
            'skip' => $skip,
            'fail' => $fail,
            'errors' => array_slice($errors, 0, 5),
        ], $this->batchMsg($done, $skip, $fail));
    }

    public function endpoint(): string
    {
        $custom = $this->opt('ai_endpoint');
        if ($custom !== '') {
            return $custom;
        }
        $kind = $this->settings->aiProviderKind($this->opt('ai_provider'));

        return match ($kind) {
            'openai' => 'https://api.openai.com/v1/chat/completions',
            'qwen' => 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions',
            'ernie' => 'https://qianfan.baidubce.com/v2/chat/completions',
            'deepseek' => 'https://api.deepseek.com/v1/chat/completions',
            'other' => '',
            default => 'https://api.openai.com/v1/chat/completions',
        };
    }

    public function defaultModel(): string
    {
        $kind = $this->settings->aiProviderKind($this->opt('ai_provider'));

        return match ($kind) {
            'qwen' => 'qwen-plus',
            'ernie' => 'ernie-4.0-8k',
            'deepseek' => 'deepseek-chat',
            default => 'gpt-4.1-mini',
        };
    }

    /** @return array{code:int,msg:string,data:array<string,mixed>} */
    private function chat(string $system, string $user, int $maxTokens): array
    {
        $key = $this->opt('ai_key');
        if ($key === '') {
            return Result::fail('未配置 API Key');
        }
        $endpoint = $this->endpoint();
        if ($endpoint === '') {
            return Result::fail('未配置兼容接口地址');
        }
        $model = $this->opt('ai_model') ?: $this->defaultModel();
        try {
            $res = HttpSsl::apply(Http::timeout(45)->withToken($key)->acceptJson())
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => $maxTokens,
                ]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if ($msg !== '' && (str_contains($msg, 'SSL certificate') || str_contains($msg, 'cURL error 60'))) {
                $msg = '无法校验证书，连接模型失败。';
            }

            return Result::fail($msg !== '' ? $msg : '模型请求失败');
        }
        $json = $res->json();
        $text = '';
        if (is_array($json)) {
            $text = (string) ($json['choices'][0]['message']['content'] ?? '');
            if ($text === '') {
                $text = (string) ($json['output_text'] ?? '');
            }
        }
        $text = trim($text);
        if ($text === '') {
            $err = is_array($json) ? (string) ($json['error']['message'] ?? $json['message'] ?? '') : '';
            if ($err === '' && ! $res->successful()) {
                $err = 'HTTP '.$res->status();
            }

            return Result::fail($err !== '' ? $err : '模型没有返回内容');
        }

        return Result::success(['text' => $text]);
    }

    /** @return array{title:string,keywords:string,description:string}|null */
    private function parseSeoJson(string $text): ?array
    {
        $raw = trim($text);
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/is', $raw, $m)) {
            $raw = $m[1];
        }
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $raw = substr($raw, $start, $end - $start + 1);
        }
        $json = json_decode($raw, true);
        if (! is_array($json)) {
            return $this->parseSeoLines($text);
        }
        $title = trim((string) ($json['title'] ?? $json['seo_title'] ?? $json['标题'] ?? ''));
        $keywords = trim((string) ($json['keywords'] ?? $json['seo_keywords'] ?? $json['关键词'] ?? ''));
        $description = trim((string) ($json['description'] ?? $json['seo_description'] ?? $json['描述'] ?? ''));
        if ($title === '' && $keywords === '' && $description === '') {
            return $this->parseSeoLines($text);
        }

        return $this->clipSeo($title, $keywords, $description);
    }

    /** @return array{title:string,keywords:string,description:string}|null */
    private function parseSeoLines(string $text): ?array
    {
        $title = '';
        $keywords = '';
        $description = '';
        foreach (preg_split('/\r\n|\n|\r/', $text) ?: [] as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }
            if ($title === '' && preg_match('/^(title|seo_title|标题)\s*[:：]\s*(.+)$/iu', $line, $m)) {
                $title = trim($m[2]);
            } elseif ($keywords === '' && preg_match('/^(keywords|seo_keywords|关键词)\s*[:：]\s*(.+)$/iu', $line, $m)) {
                $keywords = trim($m[2]);
            } elseif ($description === '' && preg_match('/^(description|seo_description|描述|摘要)\s*[:：]\s*(.+)$/iu', $line, $m)) {
                $description = trim($m[2]);
            }
        }
        if ($title === '' && $keywords === '' && $description === '') {
            return null;
        }

        return $this->clipSeo($title, $keywords, $description);
    }

    /** @return array{title:string,keywords:string,description:string} */
    private function clipSeo(string $title, string $keywords, string $description): array
    {
        $keywords = trim(preg_replace('/[，、;；]+/u', ',', $keywords) ?? $keywords, " \t,");

        return [
            'title' => mb_substr($title, 0, 80),
            'keywords' => mb_substr($keywords, 0, 255),
            'description' => mb_substr($description, 0, 500),
        ];
    }

    /** @param  array<string, mixed>  $video */
    public function videoCtx(VideoModel|array $video): array
    {
        $row = $video instanceof VideoModel ? $video->toArray() : $video;
        $typeName = '';
        if ($video instanceof VideoModel) {
            try {
                $typeName = (string) ($video->type?->name ?? '');
            } catch (\Throwable) {
                $typeName = '';
            }
        }

        return [
            'title' => (string) ($row['title'] ?? ''),
            'hint' => (string) ($row['description'] ?? ''),
            'type' => $typeName !== '' ? $typeName : (string) ($row['type'] ?? ''),
            'year' => (string) ($row['year'] ?? ''),
            'area' => (string) ($row['area'] ?? ''),
            'actors' => (string) ($row['actors_text'] ?? $row['actor'] ?? ''),
        ];
    }

    private function seoFilled(VideoModel $video): bool
    {
        return trim((string) ($video->seo_title ?? '')) !== ''
            && trim((string) ($video->seo_keywords ?? '')) !== ''
            && trim((string) ($video->seo_description ?? '')) !== '';
    }

    private function batchMsg(int $done, int $skip, int $fail): string
    {
        $parts = [];
        if ($done > 0) {
            $parts[] = '写入 '.$done;
        }
        if ($skip > 0) {
            $parts[] = '跳过 '.$skip;
        }
        if ($fail > 0) {
            $parts[] = '失败 '.$fail;
        }

        return $parts === [] ? '没有改动' : implode('，', $parts);
    }

    private function opt(string $key): string
    {
        return trim((string) $this->settings->get($key, ''));
    }
}

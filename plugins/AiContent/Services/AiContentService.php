<?php

namespace Plugins\AiContent\Services;

use App\Services\Video\VideoSettingService;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Http;

class AiContentService
{
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
        $key = $this->opt('ai_key');
        if ($key === '') {
            return Result::fail('未配置 API Key');
        }
        $endpoint = $this->endpoint();
        if ($endpoint === '') {
            return Result::fail('未配置兼容接口地址');
        }
        $model = $this->opt('ai_model') ?: 'gpt-4.1-mini';
        $prompt = '为影视作品《'.$title.'》写一段不超过120字的中文剧情简介，不要标题、不要剧透结局、不要列表。';
        if (trim($hint) !== '') {
            $prompt .= '已有资料：'.mb_substr(trim($hint), 0, 400);
        }
        try {
            $res = Http::timeout(30)
                ->withToken($key)
                ->acceptJson()
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => '你是影视资料编辑，只输出简介正文。'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 400,
                ]);
        } catch (\Throwable $e) {
            return Result::fail($e->getMessage() !== '' ? $e->getMessage() : '模型请求失败');
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

        return Result::success(['text' => $text], '已生成');
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
            'other' => '',
            default => 'https://api.openai.com/v1/chat/completions',
        };
    }

    private function opt(string $key): string
    {
        return trim((string) $this->settings->get($key, ''));
    }
}

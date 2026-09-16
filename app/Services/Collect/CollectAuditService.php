<?php

namespace App\Services\Collect;

use App\Models\Video\VideoAuditRule;
use Illuminate\Support\Facades\Schema;

class CollectAuditService
{
    /** @return array{action:string,rule:string}|null */
    public function inspect(string $title, string $content = '', string $actor = ''): ?array
    {
        try {
            if (! Schema::hasTable('video_audit_rules')) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        $hay = [
            'title' => $title,
            'content' => $content,
            'actor' => $actor,
        ];

        $rules = VideoAuditRule::query()->where('status', 1)->orderByDesc('sort')->orderBy('id')->get();
        foreach ($rules as $rule) {
            $text = $hay[(string) $rule->scope] ?? $title;
            if (! $this->matchText($text, (string) $rule->words, (int) ($rule->is_regex ?? 0) === 1)) {
                continue;
            }
            $action = (string) $rule->action;

            return [
                'action' => in_array($action, ['review', 'replace', 'skip'], true) ? $action : 'skip',
                'rule' => (string) $rule->name,
                'words' => (string) $rule->words,
            ];
        }

        return null;
    }

    public function applyReplace(string $text, string $words): string
    {
        foreach ($this->words($words) as $word) {
            if ($word === '') {
                continue;
            }
            $text = str_ireplace($word, '', $text);
        }

        return $text;
    }

    public function matchText(string $text, string $words, bool $isRegex): bool
    {
        foreach ($this->words($words) as $word) {
            if ($word === '') {
                continue;
            }
            if ($isRegex) {
                $ok = @preg_match('/'.$word.'/iu', $text);
                if ($ok === 1) {
                    return true;
                }
            } elseif (mb_stripos($text, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    public function splitWords(string $raw): array
    {
        return $this->words($raw);
    }

    /** @return list<string> */
    private function words(string $raw): array
    {
        $parts = preg_split('/[\r\n,，]+/u', $raw) ?: [];

        return array_values(array_unique(array_filter(array_map('trim', $parts))));
    }
}

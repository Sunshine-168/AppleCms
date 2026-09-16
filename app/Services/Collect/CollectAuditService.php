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
            $matched = false;
            foreach ($this->words((string) $rule->words) as $word) {
                if ($word === '') {
                    continue;
                }
                if ((int) ($rule->is_regex ?? 0) === 1) {
                    $ok = @preg_match('/'.$word.'/iu', $text);
                    if ($ok === 1) {
                        $matched = true;
                        break;
                    }
                } elseif (mb_stripos($text, $word) !== false) {
                    $matched = true;
                    break;
                }
            }
            if (! $matched) {
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

    /** @return list<string> */
    private function words(string $raw): array
    {
        $parts = preg_split('/[\r\n,，]+/u', $raw) ?: [];

        return array_values(array_unique(array_filter(array_map('trim', $parts))));
    }
}

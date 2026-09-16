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
            foreach ($this->words((string) $rule->words) as $word) {
                if ($word !== '' && mb_stripos($text, $word) !== false) {
                    return [
                        'action' => (string) $rule->action === 'review' ? 'review' : 'skip',
                        'rule' => (string) $rule->name,
                    ];
                }
            }
        }

        return null;
    }

    /** @return list<string> */
    private function words(string $raw): array
    {
        $parts = preg_split('/[\r\n,，]+/u', $raw) ?: [];

        return array_values(array_unique(array_filter(array_map('trim', $parts))));
    }
}

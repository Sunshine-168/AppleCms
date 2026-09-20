<?php

namespace App\Models\Video;

class VideoCjRule extends VideoOpsModel
{
    protected $table = 'video_cj_rules';

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function due(): bool
    {
        if ((int) $this->status !== 1) {
            return false;
        }
        $last = (int) ($this->last_run_at ?? 0);
        if ($last < 1) {
            return true;
        }

        return $last <= time() - max(1, (int) ($this->interval_minutes ?: 60)) * 60;
    }

    public static function typeLabel(?string $type): string
    {
        return match ($type) {
            'rss' => admin_t('ui.cj_type_rss'),
            'json' => admin_t('ui.cj_type_json'),
            'regex' => admin_t('ui.cj_type_regex'),
            default => admin_t('ui.cj_type_html'),
        };
    }
}

<?php

namespace App\Services\Video;

use App\Models\Video\VideoSynonym;
use Illuminate\Support\Facades\Schema;

class SynonymService
{
    public function expand(string $kw): string
    {
        $kw = trim($kw);
        if ($kw === '' || ! Schema::hasTable('video_synonyms')) {
            return $kw;
        }
        try {
            $rows = VideoSynonym::query()
                ->where('status', 1)
                ->get(['from_word', 'to_word'])
                ->sortByDesc(fn ($row) => mb_strlen((string) $row->from_word));
        } catch (\Throwable) {
            return $kw;
        }
        foreach ($rows as $row) {
            $from = (string) $row->from_word;
            if ($from !== '') {
                $kw = str_replace($from, (string) $row->to_word, $kw);
            }
        }

        return trim($kw);
    }
}

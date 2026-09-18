<?php

namespace Plugins\Manga\Services;

use Plugins\Manga\Models\Manga;

class MangaAdmin
{
    public function __construct(
        private readonly MangaService $manga,
        private readonly MangaStatsService $stats,
    ) {}

    /** @param  array<string, mixed>  $payload */
    public function boardPayload(array $payload): array
    {
        $desk = strtolower(trim((string) ($payload['desk'] ?? 'works')));
        $workId = (int) ($payload['filterMangaId'] ?? 0);
        if ($desk === 'work') {
            if ($workId < 1) {
                $payload['desk'] = 'works';
                $payload['work'] = null;
            } else {
                $work = $this->manga->findAny($workId);
                $payload['work'] = $work ? $this->workCard($work) : null;
                if ($payload['work'] === null) {
                    $payload['desk'] = 'works';
                }
            }
        }
        $desk = strtolower(trim((string) ($payload['desk'] ?? 'works')));
        if ($desk === 'stats') {
            $payload['stats'] = $this->stats->summary();
        }

        return $payload;
    }

    /** @return array<string, mixed> */
    private function workCard(Manga $work): array
    {
        $rows = collect([$work]);
        $this->manga->decorateFrontRows($rows);

        return [
            'id' => (int) $work->id,
            'title' => (string) $work->title,
            'author' => (string) ($work->author ?? ''),
            'cover' => (string) ($work->cover ?? ''),
            'status' => (int) ($work->status ?? 0),
            'yid' => (int) ($work->yid ?? 0),
            'serialize' => (int) ($work->serialize ?? 0),
            'serialize_label' => $work->serializeLabel(),
            'chapter_count' => (int) ($work->chapter_count ?? 0),
            'latest_chapter' => $work->latest_chapter ?? null,
            'front_url' => url('/manga/'.$work->id),
            'hits' => (int) ($work->hits ?? 0),
            'remarks' => (string) ($work->remarks ?? ''),
        ];
    }
}

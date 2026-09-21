<?php

namespace App\Support;

use App\Models\Member\Member;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoTypeModel;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class AppApi
{
    public static function ok(array $data = [], string $msg = 'ok'): JsonResponse
    {
        return Ajax::message(0, $msg, $data);
    }

    public static function fail(string $msg, array $data = []): JsonResponse
    {
        return Ajax::message(1, $msg, $data);
    }

    public static function missing(string $msg = '不存在'): JsonResponse
    {
        return Ajax::message(1, $msg, []);
    }

    /**
     * @param  callable(mixed): array<string, mixed>  $map
     * @return array<string, mixed>
     */
    public static function page(LengthAwarePaginator $paginator, string $key, callable $map): array
    {
        return [
            $key => collect($paginator->items())->map($map)->values()->all(),
            'page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }

    /** @param  array<string, mixed>  $site */
    public static function site(array $site): array
    {
        return [
            'title' => (string) ($site['title'] ?? $site['site_title'] ?? config('app.name')),
            'keyword' => (string) ($site['keyword'] ?? $site['site_keyword'] ?? ''),
            'description' => (string) ($site['description'] ?? $site['site_description'] ?? ''),
            'theme' => (string) ($site['theme'] ?? config('video.theme', 'default')),
            'icp' => (string) ($site['icp'] ?? ''),
            'member_register' => (int) ($site['member_register'] ?? 1) === 1,
            'member_comment_login' => (int) ($site['member_comment_login'] ?? 0) === 1,
            'trysee_seconds' => (int) ($site['trysee_seconds'] ?? 0),
        ];
    }

    public static function type(?VideoTypeModel $type): ?array
    {
        if (! $type) {
            return null;
        }

        return [
            'id' => (int) $type->id,
            'parent_id' => (int) ($type->parent_id ?? 0),
            'name' => (string) $type->name,
            'slug' => (string) ($type->slug ?? ''),
            'mid' => (int) ($type->mid ?? 1),
            'kind' => method_exists($type, 'kind') ? (string) $type->kind() : 'list',
            'cover' => (string) ($type->cover ?? $type->pic ?? ''),
            'sort' => (int) ($type->sort ?? 0),
        ];
    }

    public static function videoCard(VideoModel $video): array
    {
        return [
            'id' => (int) $video->id,
            'title' => (string) $video->title,
            'subtitle' => (string) ($video->subtitle ?? ''),
            'cover' => (string) ($video->cover ?? ''),
            'remarks' => (string) ($video->remarks ?? ''),
            'year' => (string) ($video->year ?? ''),
            'area' => (string) ($video->area ?? ''),
            'lang' => (string) ($video->lang ?? ''),
            'class' => (string) ($video->class ?? ''),
            'score' => (float) ($video->stat?->score ?? $video->score ?? 0),
            'hits' => (int) ($video->hits ?? $video->stat?->hits ?? 0),
            'type_id' => (int) ($video->type_id ?? 0),
            'type_name' => (string) ($video->type?->name ?? ''),
            'is_recommend' => (int) ($video->is_recommend ?? 0) === 1,
            'serial' => (string) ($video->serial ?? ''),
            'updated_at' => (int) ($video->updated_at ?? 0),
        ];
    }

    public static function videoDetail(VideoModel $video, bool $favorited = false): array
    {
        $row = self::videoCard($video);
        $row['description'] = (string) ($video->description ?? '');
        $row['director'] = (string) ($video->director ?? '');
        $row['writer'] = (string) ($video->writer ?? '');
        $row['duration'] = (string) ($video->duration ?? '');
        $row['points'] = (int) ($video->points ?? 0);
        $row['total'] = (int) ($video->total ?? 0);
        $row['isend'] = (int) ($video->isend ?? 0) === 1;
        $row['favorited'] = $favorited;
        $row['actors'] = $video->relationLoaded('actors')
            ? $video->actors->map(fn ($a) => [
                'id' => (int) $a->id,
                'name' => (string) $a->name,
                'avatar' => (string) ($a->avatar ?? $a->pic ?? ''),
            ])->values()->all()
            : [];
        $row['tags'] = $video->relationLoaded('tags')
            ? $video->tags->map(fn ($t) => [
                'id' => (int) $t->id,
                'name' => (string) $t->name,
                'slug' => (string) ($t->slug ?? ''),
            ])->values()->all()
            : [];
        $row['sources'] = $video->relationLoaded('sources')
            ? $video->sources->where('status', 1)->values()->map(fn ($s) => self::source($s))->all()
            : [];
        $row['plots'] = $video->relationLoaded('plots')
            ? $video->plots->map(fn ($p) => [
                'id' => (int) $p->id,
                'title' => (string) ($p->title ?? ''),
                'episode_num' => (int) ($p->episode_num ?? 0),
                'content' => (string) ($p->content ?? ''),
            ])->values()->all()
            : [];

        return $row;
    }

    public static function source(VideoSourceModel $source): array
    {
        $episodes = $source->relationLoaded('episodes')
            ? $source->episodes->where('status', 1)->values()
            : collect();

        return [
            'id' => (int) $source->id,
            'name' => (string) $source->name,
            'type' => (string) ($source->type ?: 'play'),
            'player' => (string) ($source->player ?? ''),
            'episodes' => $episodes->map(fn ($ep) => self::episode($ep, false))->values()->all(),
        ];
    }

    public static function episode(VideoEpisodeModel $episode, bool $withUrl = false): array
    {
        $row = [
            'id' => (int) $episode->id,
            'name' => (string) ($episode->display_name ?? $episode->name ?? ''),
            'episode_num' => (int) ($episode->episode_num ?? 0),
        ];
        if ($withUrl) {
            $row['url'] = (string) ($episode->url ?? '');
        }

        return $row;
    }

    public static function art(VideoArt $art, bool $detail = false): array
    {
        $row = [
            'id' => (int) $art->id,
            'title' => (string) $art->title,
            'blurb' => (string) ($art->blurb ?? ''),
            'cover' => (string) ($art->cover ?? $art->pic ?? ''),
            'type_id' => (int) ($art->type_id ?? 0),
            'hits' => (int) ($art->hits ?? 0),
            'created_at' => (int) ($art->created_at ?? 0),
        ];
        if ($detail) {
            $row['content'] = (string) ($art->content ?? '');
            $row['author'] = (string) ($art->author ?? '');
        }

        return $row;
    }

    public static function member(Member $member): array
    {
        return [
            'id' => (int) $member->id,
            'name' => (string) $member->name,
            'email' => (string) $member->email,
            'avatar' => (string) ($member->avatar ?? ''),
            'points' => (int) ($member->points ?? 0),
            'group_id' => (int) $member->effectiveGroupId(),
            'group_expire' => $member->groupExpireLabel(),
        ];
    }

    /** @param  Collection<int, mixed>|iterable<mixed>  $items */
    public static function comments(iterable $items): array
    {
        return collect($items)->map(fn ($c) => [
            'id' => (int) $c->id,
            'author' => (string) ($c->author_name ?? ''),
            'content' => (string) ($c->content ?? ''),
            'created_at' => (int) ($c->created_at ?? 0),
        ])->values()->all();
    }
}

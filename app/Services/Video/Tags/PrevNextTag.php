<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoModel;
use App\Cms\CmsViewContext;
use Illuminate\Support\HtmlString;

class PrevNextTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    /** @param  array<string, mixed>  $options */
    public function render(string $direction, array $options = []): HtmlString
    {
        $video = $this->context->video();
        if (! $video) {
            return new HtmlString('');
        }

        $query = VideoModel::query()->published();
        if ($video->type_id) {
            $query->where('type_id', $video->type_id);
        }

        $neighbor = $direction === 'prev'
            ? $query->where('id', '<', $video->id)->orderByDesc('id')->first()
            : $query->where('id', '>', $video->id)->orderBy('id')->first();

        if (! $neighbor) {
            return new HtmlString('<span class="vod-'.$direction.'">'.e((string) ($options['msg'] ?? '没有了')).'</span>');
        }

        return new HtmlString(
            '<a class="vod-'.$direction.'" href="'.e($neighbor->url).'">'.e($neighbor->title).'</a>'
        );
    }
}

<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoTypeModel;
use App\Cms\CmsViewContext;
use Illuminate\Support\HtmlString;

class SeoTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    public function render(): HtmlString
    {
        $title = $this->context->pageTitle();
        $keywords = $this->context->seoKeywords();
        $description = $this->context->seoDescription();
        $canonical = url()->current();
        $video = $this->context->video();
        $image = $video && $video->cover ? $this->abs($video->cover) : '';

        $html = '<title>'.e($title)."</title>\n"
            .'<meta name="keywords" content="'.e($keywords)."\">\n"
            .'<meta name="description" content="'.e($description)."\">\n"
            .'<link rel="canonical" href="'.e($canonical)."\">\n"
            .'<meta property="og:type" content="'.($video ? 'video.movie' : 'website')."\">\n"
            .'<meta property="og:title" content="'.e($title)."\">\n"
            .'<meta property="og:description" content="'.e($description)."\">\n"
            .'<meta property="og:url" content="'.e($canonical).'">';
        if ($image !== '') {
            $html .= "\n".'<meta property="og:image" content="'.e($image).'">';
        }

        return new HtmlString($html);
    }

    /** @param  array<string, mixed>  $options */
    public function breadcrumb(array $options = []): HtmlString
    {
        $symbol = e((string) ($options['symbol'] ?? ' / '));
        $class = e((string) ($options['class'] ?? 'breadcrumb'));
        $parts = ['<a href="'.e(vod_url('home')).'">首页</a>'];

        $type = $this->context->type() ?? $this->context->video()?->type;
        foreach ($this->ancestors($type) as $cat) {
            $parts[] = '<a href="'.e($cat->url).'">'.e($cat->name).'</a>';
        }

        if ($video = $this->context->video()) {
            $parts[] = '<span>'.e($video->title).'</span>';
        } elseif (! empty($options['last'])) {
            $parts[] = '<span>'.e((string) $options['last']).'</span>';
        }

        return new HtmlString('<nav class="'.$class.'">'.implode($symbol, $parts).'</nav>');
    }

    /** @return list<VideoTypeModel> */
    private function ancestors(?VideoTypeModel $type): array
    {
        if (! $type) {
            return [];
        }
        $chain = [];
        $cursor = $type;
        $guard = 0;
        while ($cursor && $guard < 20) {
            array_unshift($chain, $cursor);
            $cursor = $cursor->parent_id ? VideoTypeModel::query()->find((int) $cursor->parent_id) : null;
            $guard++;
        }

        return $chain;
    }

    private function abs(string $path): string
    {
        $path = trim($path);
        if ($path === '' || preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return url($path);
    }
}

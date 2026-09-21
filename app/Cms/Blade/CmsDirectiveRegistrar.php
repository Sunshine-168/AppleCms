<?php

namespace App\Cms\Blade;

use App\Cms\CmsViewContext;
use App\Services\Video\Tags\ActorTag;
use App\Services\Video\Tags\AdTag;
use App\Services\Video\Tags\ArtTag;
use App\Services\Video\Tags\CommentTag;
use App\Services\Video\Tags\EpisodeTag;
use App\Services\Video\Tags\FilterTag;
use App\Services\Video\Tags\FormatTag;
use App\Services\Video\Tags\GuestbookTag;
use App\Services\Video\Tags\LinkTag;
use App\Services\Video\Tags\PrevNextTag;
use App\Services\Video\Tags\SeoTag;
use App\Services\Video\Tags\SlideTag;
use App\Services\Video\Tags\SourceTag;
use App\Services\Video\Tags\TagListTag;
use App\Services\Video\Tags\TopicTag;
use App\Services\Video\Tags\TypeTag;
use App\Services\Video\Tags\VodTag;
use Illuminate\Support\Facades\Blade;

class CmsDirectiveRegistrar
{
    public function register(): void
    {
        Blade::directive('vodSeo', function (): string {
            return '<?php echo app(\\'.SeoTag::class.'::class)->render(); ?>';
        });

        Blade::directive('vodBreadcrumb', function (?string $expression): string {
            $expression = $this->expressionOrEmptyArray($expression);

            return '<?php echo app(\\'.SeoTag::class.'::class)->breadcrumb('.$expression.'); ?>';
        });

        Blade::directive('vodPaginate', function (?string $expression): string {
            $expression = trim((string) $expression);
            if ($expression === '') {
                return '<?php echo app(\\'.CmsViewContext::class.'::class)->renderPaginate(); ?>';
            }

            return '<?php echo app(\\'.CmsViewContext::class.'::class)->renderPaginate('.$expression.'); ?>';
        });

        Blade::directive('vodDate', function (?string $expression): string {
            $expression = $this->expressionOrEmptyArray($expression);

            return '<?php echo app(\\'.FormatTag::class.'::class)->date('.$expression.'); ?>';
        });

        Blade::directive('vodSubstr', function (?string $expression): string {
            $expression = $this->expressionOrEmptyArray($expression);

            return '<?php echo app(\\'.FormatTag::class.'::class)->substr('.$expression.'); ?>';
        });

        Blade::directive('vodPrev', function (?string $expression): string {
            $expression = $this->expressionOrEmptyArray($expression);

            return '<?php echo app(\\'.PrevNextTag::class.'::class)->render(\'prev\', '.$expression.'); ?>';
        });

        Blade::directive('vodNext', function (?string $expression): string {
            $expression = $this->expressionOrEmptyArray($expression);

            return '<?php echo app(\\'.PrevNextTag::class.'::class)->render(\'next\', '.$expression.'); ?>';
        });

        $this->registerLoop('vod', VodTag::class);
        $this->registerLoop('vodType', TypeTag::class);
        $this->registerLoop('vodFilter', FilterTag::class);
        $this->registerLoop('vodTag', TagListTag::class);
        $this->registerLoop('vodActor', ActorTag::class);
        $this->registerLoop('vodTopic', TopicTag::class);
        $this->registerLoop('vodArt', ArtTag::class);
        $this->registerLoop('vodSource', SourceTag::class);
        $this->registerLoop('vodEpisode', EpisodeTag::class);
        $this->registerLoop('vodComment', CommentTag::class);
        $this->registerLoop('vodLink', LinkTag::class);
        $this->registerLoop('vodAd', AdTag::class);
        $this->registerLoop('vodSlide', SlideTag::class);
        $this->registerLoop('vodGbook', GuestbookTag::class);
    }

    /** @param  class-string  $tagClass */
    public function registerLoop(string $name, string $tagClass): void
    {
        Blade::directive($name, function (?string $expression) use ($tagClass): string {
            $expression = $this->expressionOrEmptyArray($expression);

            return '<?php '.
                'if (!isset($__vodLoopStack) || !is_array($__vodLoopStack)) { $__vodLoopStack = []; } '.
                '$__vodLoopFrame = [\'options\' => '.$expression.']; '.
                '$__vodLoopFrame[\'as\'] = $__vodLoopFrame[\'options\'][\'as\'] ?? \'item\'; '.
                '$__vodLoopFrame[\'items\'] = app(\\'.$tagClass.'::class)->get($__vodLoopFrame[\'options\']); '.
                '$__vodLoopStack[] = $__vodLoopFrame; '.
                'foreach ($__vodLoopFrame[\'items\'] as $__vodTagItem): '.
                '${$__vodLoopFrame[\'as\']} = $__vodTagItem; '.
                '?>';
        });

        Blade::directive('end'.$name, function (): string {
            return '<?php endforeach; array_pop($__vodLoopStack); $__vodLoopFrame = $__vodLoopStack ? $__vodLoopStack[array_key_last($__vodLoopStack)] : null; ?>';
        });
    }

    private function expressionOrEmptyArray(?string $expression): string
    {
        $expression = trim((string) $expression);

        return $expression === '' ? '[]' : $expression;
    }
}

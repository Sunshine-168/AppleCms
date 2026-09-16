<?php

namespace App\Video;

use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoTypeModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class CmsViewContext
{
    protected array $site = [];

    protected ?VideoTypeModel $type = null;

    protected ?VideoModel $video = null;

    protected ?VideoSourceModel $source = null;

    protected ?VideoEpisodeModel $episode = null;

    protected string $pageTitle = '';

    protected string $seoKeywords = '';

    protected string $seoDescription = '';

    protected ?LengthAwarePaginator $paginator = null;

    /** @var array<string, mixed> */
    protected array $filters = [];

    public function setSite(array $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function site(): array
    {
        return $this->site;
    }

    public function setType(?VideoTypeModel $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function type(): ?VideoTypeModel
    {
        return $this->type;
    }

    public function setVideo(?VideoModel $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function video(): ?VideoModel
    {
        return $this->video;
    }

    public function setSource(?VideoSourceModel $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function source(): ?VideoSourceModel
    {
        return $this->source;
    }

    public function setEpisode(?VideoEpisodeModel $episode): self
    {
        $this->episode = $episode;

        return $this;
    }

    public function episode(): ?VideoEpisodeModel
    {
        return $this->episode;
    }

    public function setFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function filters(): array
    {
        return $this->filters;
    }

    public function setSeo(string $title, string $keywords = '', string $description = ''): self
    {
        $this->pageTitle = $title;
        $this->seoKeywords = $keywords;
        $this->seoDescription = $description;

        return $this;
    }

    public function pageTitle(): string
    {
        return $this->pageTitle !== ''
            ? $this->pageTitle
            : (string) ($this->site['title'] ?? config('app.name'));
    }

    public function seoKeywords(): string
    {
        return $this->seoKeywords;
    }

    public function seoDescription(): string
    {
        return $this->seoDescription;
    }

    public function setPaginator(?LengthAwarePaginator $paginator): self
    {
        $this->paginator = $paginator;

        return $this;
    }

    public function paginator(): ?LengthAwarePaginator
    {
        return $this->paginator;
    }

    public function renderPaginate(?string $view = null): Htmlable
    {
        if (! $this->paginator) {
            return new HtmlString('');
        }

        $theme = config('video.theme', 'default');
        $view = $view ?: "themes.{$theme}.partials.paginate";
        if (! view()->exists($view)) {
            return new HtmlString($this->paginator->links()->toHtml());
        }

        return new HtmlString(view($view, ['paginator' => $this->paginator])->render());
    }
}

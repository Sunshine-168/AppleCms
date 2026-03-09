<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\TagService;

class MaccmsTag extends Component
{
    public $tag;
    public $params;
    public $list;
    public $paging;

    /**
     * Create a new component instance.
     */
    public function __construct($tag, $params = [])
    {
        $this->tag = $tag;
        $this->params = $params;
        $this->loadData();
    }

    /**
     * 加载标签数据
     */
    protected function loadData()
    {
        $tagService = app(TagService::class);

        switch ($this->tag) {
            case 'link':
                $this->list = $tagService->getLinkList($this->params);
                break;
            case 'area':
                $this->list = $tagService->getAreaList($this->params);
                break;
            case 'lang':
                $this->list = $tagService->getLangList($this->params);
                break;
            case 'year':
                $this->list = $tagService->getYearList($this->params);
                break;
            case 'class':
                $this->list = $tagService->getClassList($this->params);
                break;
            case 'version':
                $this->list = $tagService->getVersionList($this->params);
                break;
            case 'state':
                $this->list = $tagService->getStateList($this->params);
                break;
            case 'letter':
                $this->list = $tagService->getLetterList($this->params);
                break;
            case 'type':
                $this->list = $tagService->getTypeList($this->params);
                break;
            case 'vod':
                $this->list = $tagService->getVodList($this->params);
                break;
            case 'art':
                $this->list = $tagService->getArtList($this->params);
                break;
            case 'actor':
                $this->list = $tagService->getActorList($this->params);
                break;
            case 'topic':
                $this->list = $tagService->getTopicList($this->params);
                break;
            case 'comment':
                $this->list = $tagService->getCommentList($this->params);
                break;
            case 'gbook':
                $this->list = $tagService->getGbookList($this->params);
                break;
            case 'role':
                $this->list = $tagService->getRoleList($this->params);
                break;
            case 'website':
                $this->list = $tagService->getWebsiteList($this->params);
                break;
            default:
                $this->list = [];
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.maccms-tag');
    }
}

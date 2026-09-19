<?php

namespace Plugins\Live\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Plugins\Live\Models\LiveCategory;
use Plugins\Live\Models\LiveChannel;

class LiveChannelAdminController extends Controller
{
    /** 打开新增频道页。 */
    public function create(Request $request): View|Factory
    {
        $pending = (string) $request->query('desk', '') === 'pending';

        return view('live::admin.channel_page', $this->formPayload([
            'title' => '',
            'sub' => '',
            'slug' => '',
            'cover' => '',
            'urls' => '',
            'play_from' => 'hls',
            'cate_id' => (int) $request->query('cate_id', 0),
            'hits' => 0,
            'recommend' => 0,
            'sort' => 0,
            'status' => $pending ? 0 : 1,
            'remarks' => '',
            'content' => '',
        ], false));
    }

    /** 打开编辑频道页。 */
    public function edit(int $id): View|Factory
    {
        $row = LiveChannel::query()->find($id);
        if (! $row) {
            abort(404);
        }

        return view('live::admin.channel_page', $this->formPayload([
            'id' => (int) $row->id,
            'title' => (string) $row->title,
            'sub' => (string) ($row->sub ?? ''),
            'slug' => (string) ($row->slug ?? ''),
            'cover' => (string) ($row->cover ?? ''),
            'urls' => (string) ($row->urls ?? ''),
            'play_from' => (string) ($row->play_from ?: 'hls'),
            'cate_id' => (int) ($row->cate_id ?? 0),
            'hits' => (int) ($row->hits ?? 0),
            'recommend' => Schema::hasColumn('plugin_live_channels', 'recommend')
                ? (int) ($row->recommend ?? 0) : 0,
            'sort' => (int) ($row->sort ?? 0),
            'status' => (int) ($row->status ?? 1),
            'remarks' => (string) ($row->remarks ?? ''),
            'content' => (string) ($row->content ?? ''),
            'front_url' => url('/live/'.$row->id),
        ], true));
    }

    /**
     * @param  array<string, mixed>  $channel
     * @return array<string, mixed>
     */
    private function formPayload(array $channel, bool $isEdit): array
    {
        return [
            'channel' => $channel,
            'isEdit' => $isEdit,
            'categories' => LiveCategory::query()->orderByDesc('sort')->orderBy('id')->get(['id', 'name']),
            'hasRecommend' => Schema::hasColumn('plugin_live_channels', 'recommend'),
        ];
    }
}

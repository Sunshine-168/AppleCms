<?php

namespace Plugins\Live\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Plugins\Live\Models\LiveCategory;
use Plugins\Live\Models\LiveChannel;

class LiveCategoryAdminController extends Controller
{
    /** 打开新增分类页。 */
    public function create(): View|Factory
    {
        return view('live::admin.category_page', $this->formPayload([
            'name' => '',
            'slug' => '',
            'pic' => '',
            'sort' => 0,
            'status' => 1,
            'channel_count' => 0,
        ], false));
    }

    /** 打开编辑分类页。 */
    public function edit(int $id): View|Factory
    {
        $row = LiveCategory::query()->find($id);
        if (! $row) {
            abort(404);
        }

        return view('live::admin.category_page', $this->formPayload([
            'id' => (int) $row->id,
            'name' => (string) $row->name,
            'slug' => (string) ($row->slug ?? ''),
            'pic' => (string) ($row->pic ?? ''),
            'sort' => (int) ($row->sort ?? 0),
            'status' => (int) ($row->status ?? 1),
            'channel_count' => (int) LiveChannel::query()->where('cate_id', $id)->count(),
        ], true));
    }

    /**
     * @param  array<string, mixed>  $category
     * @return array<string, mixed>
     */
    private function formPayload(array $category, bool $isEdit): array
    {
        return [
            'category' => $category,
            'isEdit' => $isEdit,
        ];
    }
}

<?php

namespace Plugins\Manga\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\MangaType;

class MangaTypeAdminController extends Controller
{
    public function __construct(private readonly SiteModuleService $modules) {}

    public function index(): View|Factory
    {
        return view('manga::admin.types');
    }

    public function create(Request $request): View|Factory
    {
        $parentId = (int) $request->query('parent_id', 0);
        $parents = $this->parentOptions(null);
        $parent = $this->findParent($parents, $parentId);
        if ($parentId > 0 && $parent === null) {
            $parentId = 0;
        }

        return view('manga::admin.type_form', [
            'type' => [
                'parent_id' => $parentId,
                'sort' => 0,
                'status' => 1,
                'page_size' => 0,
            ],
            'isEdit' => false,
            'parents' => $parents,
            'parent' => $parent,
        ]);
    }

    public function edit(int $id): View|Factory|RedirectResponse
    {
        if (! Schema::hasTable('plugin_manga_types')) {
            abort(404);
        }
        $row = MangaType::query()->find($id);
        if (! $row) {
            abort(404);
        }
        $type = $row->toArray();
        $parents = $this->parentOptions($id);
        $parentId = (int) ($type['parent_id'] ?? 0);

        return view('manga::admin.type_form', [
            'type' => $type,
            'isEdit' => true,
            'parents' => $parents,
            'parent' => $this->findParent($parents, $parentId),
        ]);
    }

    /** @return list<array{id:int,name:string,depth:int,parent_id:int}> */
    private function parentOptions(?int $excludeId): array
    {
        $res = $this->modules->lists('manga_types', ['limit' => 500]);
        $rows = $res['data']['data'] ?? [];
        if (! is_array($rows)) {
            return [];
        }
        $skip = [];
        $ex = (int) ($excludeId ?? 0);
        if ($ex > 0) {
            $skip[$ex] = true;
        }
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = (int) ($row['id'] ?? 0);
            $pid = (int) ($row['parent_id'] ?? 0);
            if ($pid > 0 && isset($skip[$pid])) {
                $skip[$id] = true;
            }
            if (isset($skip[$id])) {
                continue;
            }
            $out[] = [
                'id' => $id,
                'name' => (string) ($row['name'] ?? ''),
                'depth' => (int) ($row['depth'] ?? 0),
                'parent_id' => $pid,
            ];
        }

        return $out;
    }

    /**
     * @param  list<array{id:int,name:string,depth:int,parent_id:int}>  $parents
     * @return array{id:int,name:string,depth:int,parent_id:int}|null
     */
    private function findParent(array $parents, int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        foreach ($parents as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }
}

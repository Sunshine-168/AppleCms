<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TypeSaveRequest;
use App\Models\Type;
use App\Models\Vod;
use App\Models\Art;
use App\Models\Actor;
use App\Models\Website;
use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TypeController extends Controller
{
    public function index(Request $request)
    {
        $where = [];
        $types = Type::where($where)
                    ->orderBy('type_sort', 'asc')
                    ->get();
        
        // Build tree structure
        $typeTree = $this->buildTypeTree($types);
        
        // Count items per type
        $listCount = [];
        
        // Video counts
        $vodCounts = Vod::select('type_id_1', 'type_id', DB::raw('count(vod_id) as cc'))
                        ->groupBy('type_id_1', 'type_id')
                        ->get();
        foreach ($vodCounts as $v) {
            $listCount[$v->type_id_1] = ($listCount[$v->type_id_1] ?? 0) + $v->cc;
            $listCount[$v->type_id] = $v->cc;
        }
        
        // Article counts
        $artCounts = Art::select('type_id_1', 'type_id', DB::raw('count(art_id) as cc'))
                       ->groupBy('type_id_1', 'type_id')
                       ->get();
        foreach ($artCounts as $v) {
            $listCount[$v->type_id_1] = ($listCount[$v->type_id_1] ?? 0) + $v->cc;
            $listCount[$v->type_id] = $v->cc;
        }
        
        // Actor counts
        $actorCounts = Actor::select('type_id_1', 'type_id', DB::raw('count(actor_id) as cc'))
                           ->groupBy('type_id_1', 'type_id')
                           ->get();
        foreach ($actorCounts as $v) {
            $listCount[$v->type_id_1] = ($listCount[$v->type_id_1] ?? 0) + $v->cc;
            $listCount[$v->type_id] = $v->cc;
        }
        
        // Website counts
        $websiteCounts = Website::select('type_id_1', 'type_id', DB::raw('count(website_id) as cc'))
                               ->groupBy('type_id_1', 'type_id')
                               ->get();
        foreach ($websiteCounts as $v) {
            $listCount[$v->type_id_1] = ($listCount[$v->type_id_1] ?? 0) + $v->cc;
            $listCount[$v->type_id] = $v->cc;
        }
        
        // Manga counts
        $mangaCounts = Manga::select('type_id_1', 'type_id', DB::raw('count(manga_id) as cc'))
                           ->groupBy('type_id_1', 'type_id')
                           ->get();
        foreach ($mangaCounts as $v) {
            $listCount[$v->type_id_1] = ($listCount[$v->type_id_1] ?? 0) + $v->cc;
            $listCount[$v->type_id] = $v->cc;
        }
        
        // Add counts to tree
        $this->addCountsToTree($typeTree, $listCount);
        
        return view('admin.type.index', compact('typeTree'));
    }

    public function info(TypeSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            if (!empty($id)) {
                $type = Type::findOrFail($id);
                $type->update($data);
            } else {
                $type = Type::create($data);
            }
            
            // Update cache
            $this->updateTypeCache();
            
            return redirect()->route('admin.type.index')->with('success', '保存成功');
        }
        
        $type = $id ? Type::findOrFail($id) : new Type();
        $pid = $request->input('pid', 0);
        $parentType = $pid ? Type::find($pid) : null;
        
        $parentTypes = Type::where('type_pid', 0)
                          ->orderBy('type_sort', 'asc')
                          ->get();
        
        return view('admin.type.info', compact('type', 'parentType', 'pid', 'parentTypes'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->withErrors(['msg' => '请选择要删除的数据']);
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        
        // Check if types have children or data
        foreach ($ids as $id) {
            $type = Type::findOrFail($id);
            
            // Check for children
            $hasChildren = Type::where('type_pid', $id)->exists();
            if ($hasChildren) {
                return back()->withErrors(['msg' => "分类 {$type->type_name} 还有子分类，请先删除子分类"]);
            }
            
            // Check for data
            $hasVod = Vod::where('type_id', $id)->orWhere('type_id_1', $id)->exists();
            $hasArt = Art::where('type_id', $id)->orWhere('type_id_1', $id)->exists();
            
            if ($hasVod || $hasArt) {
                return back()->withErrors(['msg' => "分类 {$type->type_name} 还有数据，请先删除或转移数据"]);
            }
        }
        
        Type::whereIn('type_id', $ids)->delete();
        $this->updateTypeCache();
        
        return back()->with('success', '删除成功');
    }

    private function buildTypeTree($types)
    {
        $tree = [];
        $map = [];
        
        // First pass: create map
        foreach ($types as $type) {
            $map[$type->type_id] = $type;
            $type->children = [];
        }
        
        // Second pass: build tree
        foreach ($types as $type) {
            if ($type->type_pid == 0) {
                $tree[] = $type;
            } else {
                if (isset($map[$type->type_pid])) {
                    $map[$type->type_pid]->children[] = $type;
                }
            }
        }
        
        return $tree;
    }

    private function addCountsToTree(&$tree, $counts)
    {
        foreach ($tree as &$item) {
            $item->cc = intval($counts[$item->type_id] ?? 0);
            if (!empty($item->children)) {
                $this->addCountsToTree($item->children, $counts);
            }
        }
    }

    private function updateTypeCache()
    {
        // Clear and rebuild type cache
        // This would typically use Laravel's cache system
        cache()->forget('type_list');
        cache()->forget('type_tree');
    }
}

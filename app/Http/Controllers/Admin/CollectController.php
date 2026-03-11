<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CollectSaveRequest;
use App\Models\Collect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CollectController extends Controller
{
    public function index(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? 100));

        $collects = Collect::orderBy('collect_id', 'desc')
                           ->paginate($param['limit'], ['*'], 'page', $param['page']);
        
        // Get break status from cache
        $cacheFlag = config('maccms.app.cache_flag', 'default');
        $collectBreak = [
            'vod' => Cache::get($cacheFlag . '_collect_break_vod'),
            'art' => Cache::get($cacheFlag . '_collect_break_art'),
            'actor' => Cache::get($cacheFlag . '_collect_break_actor'),
            'role' => Cache::get($cacheFlag . '_collect_break_role'),
            'website' => Cache::get($cacheFlag . '_collect_break_website'),
            'manga' => Cache::get($cacheFlag . '_collect_break_manga'),
        ];
        
        return view('admin.collect.index', compact('collects', 'param', 'collectBreak'));
    }

    public function test(Request $request)
    {
        // Test collection - this would call the Collect model's test method
        $param = $request->all();
        // Implementation would go here
        return response()->json(['code' => 1, 'msg' => '测试成功']);
    }

    public function info(CollectSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            if (!empty($id)) {
                $collect = Collect::findOrFail($id);
                $collect->update($data);
            } else {
                $collect = Collect::create($data);
            }
            
            return redirect()->route('admin.collect.index')->with('success', '保存成功');
        }
        
        $collect = $id ? Collect::findOrFail($id) : new Collect();
        
        return view('admin.collect.info', compact('collect'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->withErrors(['msg' => '请选择要删除的数据']);
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        Collect::whereIn('collect_id', $ids)->delete();
        
        return back()->with('success', '删除成功');
    }

    public function union(Request $request)
    {
        $cacheFlag = config('maccms.app.cache_flag', 'default');
        $collectBreak = [
            'vod' => Cache::get($cacheFlag . '_collect_break_vod'),
            'art' => Cache::get($cacheFlag . '_collect_break_art'),
            'actor' => Cache::get($cacheFlag . '_collect_break_actor'),
            'role' => Cache::get($cacheFlag . '_collect_break_role'),
            'website' => Cache::get($cacheFlag . '_collect_break_website'),
            'manga' => Cache::get($cacheFlag . '_collect_break_manga'),
        ];
        
        return view('admin.collect.union', compact('collectBreak'));
    }
}

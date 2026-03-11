<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VodSaveRequest;
use App\Models\Vod;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class VodController extends Controller
{
    public function __construct()
    {
        // Add admin authentication middleware
    }

    public function index(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? 20));

        $where = [];
        
        if (!empty($param['type'])) {
            $where[] = function($query) use ($param) {
                $query->where('type_id', $param['type'])
                      ->orWhere('type_id_1', $param['type']);
            };
        }
        
        if (!empty($param['level'])) {
            $where['vod_level'] = $param['level'];
        }
        
        if (isset($param['status']) && in_array($param['status'], ['0', '1'])) {
            $where['vod_status'] = $param['status'];
        }
        
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query = Vod::where(function($q) use ($wd) {
                $q->where('vod_name', 'like', '%' . $wd . '%')
                  ->orWhere('vod_actor', 'like', '%' . $wd . '%')
                  ->orWhere('vod_sub', 'like', '%' . $wd . '%');
            });
        } else {
            $query = Vod::query();
        }
        
        foreach ($where as $key => $value) {
            if (is_callable($value)) {
                $query->where($value);
            } else {
                $query->where($key, $value);
            }
        }
        
        $order = 'vod_time desc';
        if (!empty($param['order']) && in_array($param['order'], ['vod_id', 'vod_hits', 'vod_hits_month', 'vod_hits_week', 'vod_hits_day'])) {
            $order = $param['order'] . ' desc';
        }
        
        $videos = $query->orderByRaw($order)
                       ->with('type')
                       ->paginate($param['limit'], ['*'], 'page', $param['page']);
        
        $types = Type::where('type_mid', 1)->get();
        $typeTree = $this->buildTypeTree($types);
        
        return view('admin.vod.index', compact('videos', 'typeTree', 'param'));
    }

    public function info(VodSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            // Handle save
            $data = $request->all();
            
            if (!empty($id)) {
                $vod = Vod::findOrFail($id);
                $vod->update($data);
            } else {
                $vod = Vod::create($data);
            }
            
            return redirect()->route('admin.vod.index')->with('success', '保存成功');
        }
        
        $vod = $id ? Vod::with('type')->findOrFail($id) : new Vod();
        $types = Type::where('type_mid', 1)->get();
        
        return view('admin.vod.info', compact('vod', 'types'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->withErrors(['msg' => '请选择要删除的数据']);
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        Vod::whereIn('vod_id', $ids)->delete();
        
        return back()->with('success', '删除成功');
    }

    private function buildTypeTree($types)
    {
        $tree = [];
        foreach ($types as $type) {
            if ($type->type_pid == 0) {
                $tree[$type->type_id] = $type;
                $tree[$type->type_id]->children = [];
            }
        }
        
        foreach ($types as $type) {
            if ($type->type_pid > 0 && isset($tree[$type->type_pid])) {
                $tree[$type->type_pid]->children[] = $type;
            }
        }
        
        return $tree;
    }
}

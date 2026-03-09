<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArtSaveRequest;
use App\Models\Art;
use App\Models\Type;
use Illuminate\Http\Request;

class ArtController extends Controller
{
    public function index(Request $request)
    {
        $query = Art::query();

        // Filter by Type
        if ($request->has('type') && $request->type) {
            $query->where(function($q) use ($request) {
                $q->where('type_id', $request->type)
                  ->orWhere('type_id_1', $request->type);
            });
        }

        // Filter by Level
        if ($request->has('level') && $request->level !== null) {
            $query->where('art_level', $request->level);
        }

        // Filter by Status
        if ($request->has('status') && $request->status !== null) {
            $query->where('art_status', $request->status);
        }

        // Filter by Lock
        if ($request->has('lock') && $request->lock !== null) {
            $query->where('art_lock', $request->lock);
        }

        // Search by keyword
        if ($request->has('wd') && $request->wd) {
            $query->where('art_name', 'like', '%' . $request->wd . '%');
        }

        $query->orderBy('art_time', 'desc');
        
        $limit = $request->input('limit', 20);
        $list = $query->paginate($limit);

        // Get Types for dropdown (Assuming type_mid=2 for Articles)
        $type_tree = Type::where('type_mid', 2)->get();

        return view('admin.art.index', compact('list', 'type_tree'));
    }

    public function info(ArtSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->except(['_token', 'file']); // Exclude file if handled separately, but usually it's a path string

            // Handle type_id_1 (parent type id)
            if (isset($data['type_id'])) {
                $type = Type::find($data['type_id']);
                if ($type) {
                    $data['type_id_1'] = $type->type_pid == 0 ? $type->type_id : $type->type_pid;
                }
            }

            // Timestamps
            if (empty($data['art_time'])) {
                $data['art_time'] = time();
            } else {
                $data['art_time'] = strtotime($data['art_time']);
            }
            
            if (empty($data['art_time_add'])) {
                $data['art_time_add'] = time();
            }
            
            // Checkboxes handling
            $data['art_status'] = $request->has('art_status') ? 1 : 0;
            $data['art_lock'] = $request->has('art_lock') ? 1 : 0;

            if ($id) {
                Art::where('art_id', $id)->update($data);
            } else {
                Art::create($data);
            }
            
            return redirect()->route('admin.art.index')->with('success', 'Saved successfully');
        }

        $info = $id ? Art::find($id) : new Art();
        
        $type_tree = Type::where('type_mid', 2)->get();

        return view('admin.art.info', compact('info', 'type_tree'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) return response()->json(['code' => 1001, 'msg' => 'Param error']);

        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        Art::destroy($ids);
        return response()->json(['code' => 1, 'msg' => 'Deleted successfully']);
    }
}

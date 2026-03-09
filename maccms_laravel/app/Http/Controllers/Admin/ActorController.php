<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActorSaveRequest;
use App\Models\Actor;
use App\Models\Type;
use Illuminate\Http\Request;

class ActorController extends Controller
{
    public function index(Request $request)
    {
        $query = Actor::query();
        
        // Filter by Type
        if ($request->has('type') && $request->type) {
            $query->where(function($q) use ($request) {
                $q->where('type_id', $request->type)
                  ->orWhere('type_id_1', $request->type);
            });
        }

        // Filter by Level
        if ($request->has('level') && $request->level) {
            $query->where('actor_level', $request->level);
        }

        // Filter by Status
        if ($request->has('status') && in_array($request->status, ['0', '1'])) {
            $query->where('actor_status', $request->status);
        }

        // Filter by Picture status
        if ($request->has('pic') && $request->pic) {
            if ($request->pic == '1') {
                $query->where('actor_pic', '');
            } elseif ($request->pic == '2') {
                $query->where('actor_pic', 'like', 'http%');
            } elseif ($request->pic == '3') {
                $query->where('actor_pic', 'like', '%#err%');
            }
        }

        // Search by Name
        if ($request->has('wd') && $request->wd) {
            $query->where('actor_name', 'like', '%' . $request->wd . '%');
        }

        // Default order
        $query->orderBy('actor_time', 'desc');

        // Pagination
        $limit = $request->input('limit', 20);
        $list = $query->paginate($limit);

        // Get Type Tree (Simplified for now)
        $type_tree = Type::where('type_mid', 8)->get(); // Assuming mid 8 is for actors

        return view('admin.actor.index', compact('list', 'type_tree'));
    }

    public function info(ActorSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            if ($id) {
                $actor = Actor::find($id);
                if (!$actor) return back()->withErrors(['msg' => 'Actor not found']);
                $actor->update($data);
            } else {
                Actor::create($data);
            }
            
            return redirect()->route('admin.actor.index')->with('success', 'Saved successfully');
        }

        $info = $id ? Actor::find($id) : new Actor();
        $type_tree = Type::where('type_mid', 8)->get();

        return view('admin.actor.info', compact('info', 'type_tree'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) return response()->json(['code' => 1001, 'msg' => 'Param error']);

        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        Actor::destroy($ids);
        return response()->json(['code' => 1, 'msg' => 'Deleted successfully']);
    }
}

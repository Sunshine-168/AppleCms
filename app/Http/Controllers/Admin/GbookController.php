<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gbook;
use Illuminate\Http\Request;

class GbookController extends Controller
{
    public function index(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? 20));

        $query = Gbook::query();
        
        if (isset($param['status']) && in_array($param['status'], ['0', '1'], true)) {
            $query->where('gbook_status', $param['status']);
        }
        
        if (isset($param['type']) && in_array($param['type'], ['1', '2'])) {
            if ($param['type'] == 1) {
                $query->where('gbook_rid', 0);
            } elseif ($param['type'] == 2) {
                $query->where('gbook_rid', '>', 0);
            }
        }
        
        if (!empty($param['reply'])) {
            $query->where('gbook_reply_time', '>', 0);
        }
        
        if (!empty($param['uid'])) {
            $query->where('user_id', $param['uid']);
        }
        
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query->where(function($q) use ($wd) {
                $q->where('gbook_name', 'like', '%' . $wd . '%')
                  ->orWhere('gbook_content', 'like', '%' . $wd . '%');
            });
        }
        
        $gbooks = $query->orderBy('gbook_id', 'desc')
                       ->paginate($param['limit'], ['*'], 'page', $param['page']);
        
        return view('admin.gbook.index', compact('gbooks', 'param'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->withErrors(['msg' => '请选择要删除的数据']);
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        Gbook::whereIn('gbook_id', $ids)->delete();
        
        return back()->with('success', '删除成功');
    }
}

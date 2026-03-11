<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GroupSaveRequest;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $param = $request->all();
        
        $query = Group::query();
        
        if (isset($param['status']) && in_array($param['status'], ['0', '1'], true)) {
            $query->where('group_status', $param['status']);
        }
        
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query->where('group_name', 'like', '%' . $wd . '%');
        }
        
        $groups = $query->orderBy('group_id', 'asc')->get();
        
        return view('admin.group.index', compact('groups', 'param'));
    }

    public function info(GroupSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            $config = config('maccms');
            if ($config['user']['reg_group'] == $data['group_id']) {
                $data['group_status'] = 1;
            }
            
            if (!empty($id)) {
                $group = Group::findOrFail($id);
                $group->update($data);
            } else {
                $group = Group::create($data);
            }
            
            return redirect()->route('admin.group.index')->with('success', '保存成功');
        }
        
        $group = $id ? Group::findOrFail($id) : new Group();
        
        return view('admin.group.info', compact('group'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->withErrors(['msg' => '请选择要删除的数据']);
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        Group::whereIn('group_id', $ids)->delete();
        
        return back()->with('success', '删除成功');
    }
}

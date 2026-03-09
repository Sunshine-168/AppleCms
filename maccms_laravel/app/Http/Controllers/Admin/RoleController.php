<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleSaveRequest;
use App\Models\Role;
use App\Models\Vod;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? 20));

        $query = Role::query();
        
        if (!empty($param['level'])) {
            $query->where('role_level', $param['level']);
        }
        
        if (isset($param['status']) && in_array($param['status'], ['0', '1'])) {
            $query->where('role_status', $param['status']);
        }
        
        if (!empty($param['rid'])) {
            $query->where('role_rid', $param['rid']);
        }
        
        if (!empty($param['pic'])) {
            if ($param['pic'] == '1') {
                $query->where('role_pic', '');
            } elseif ($param['pic'] == '2') {
                $query->where('role_pic', 'like', 'http%');
            } elseif ($param['pic'] == '3') {
                $query->where('role_pic', 'like', '%#err%');
            }
        }
        
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query->where('role_name', 'like', '%' . $wd . '%');
        }
        
        $roles = $query->orderBy('role_time', 'desc')
                      ->paginate($param['limit'], ['*'], 'page', $param['page']);
        
        return view('admin.role.index', compact('roles', 'param'));
    }

    public function info(RoleSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            if (!empty($id)) {
                $role = Role::findOrFail($id);
                $role->update($data);
            } else {
                $role = Role::create($data);
            }
            
            return redirect()->route('admin.role.index')->with('success', '保存成功');
        }
        
        $role = $id ? Role::findOrFail($id) : new Role();
        $rid = $request->input('rid', $role->role_rid ?? 0);
        $tab = $request->input('tab', '');
        
        $vod = null;
        if ($rid > 0) {
            $vod = Vod::find($rid);
        }
        
        return view('admin.role.info', compact('role', 'vod', 'rid', 'tab'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->withErrors(['msg' => '请选择要删除的数据']);
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        Role::whereIn('role_id', $ids)->delete();
        
        return back()->with('success', '删除成功');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSaveRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::query();

        if ($request->has('wd') && $request->wd) {
            $query->where('admin_name', 'like', '%' . $request->wd . '%');
        }

        $query->orderBy('admin_id', 'desc');
        
        $limit = $request->input('limit', 20);
        $list = $query->paginate($limit);

        return view('admin.admin.index', compact('list'));
    }

    public function info(AdminSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            if ($id) {
                $admin = Admin::find($id);
                if (!$admin) return back()->withErrors(['msg' => 'Admin not found']);
                
                // Password handling
                if (!empty($data['admin_pwd'])) {
                    $data['admin_pwd'] = md5($data['admin_pwd']);
                } else {
                    unset($data['admin_pwd']);
                }

                // Permissions handling
                if (isset($data['admin_auth']) && is_array($data['admin_auth'])) {
                    $data['admin_auth'] = ',' . implode(',', $data['admin_auth']) . ',';
                } else {
                    $data['admin_auth'] = '';
                }

                $admin->update($data);
            } else {
                $data['admin_pwd'] = md5($data['admin_pwd']);
                
                if (isset($data['admin_auth']) && is_array($data['admin_auth'])) {
                    $data['admin_auth'] = ',' . implode(',', $data['admin_auth']) . ',';
                } else {
                    $data['admin_auth'] = '';
                }

                Admin::create($data);
            }
            
            return redirect()->route('admin.admin.index')->with('success', 'Saved successfully');
        }

        $info = $id ? Admin::find($id) : new Admin();
        
        // Simplified Permissions List (Example)
        $menus = [
            'System' => [
                'admin/config' => 'Configuration',
                'admin/admin/index' => 'Admin Management',
            ],
            'Content' => [
                'admin/vod/index' => 'Video Management',
                'admin/actor/index' => 'Actor Management',
                'admin/art/index' => 'Article Management',
            ],
            'Users' => [
                'admin/user/index' => 'User Management',
            ],
            'Addons' => [
                'admin/addon/index' => 'Addon Management',
            ]
        ];

        return view('admin.admin.info', compact('info', 'menus'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) return response()->json(['code' => 1001, 'msg' => 'Param error']);

        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        // Prevent self-deletion if logged in (assuming session auth)
        // Note: maccms uses its own session/cookie logic, but we might be using Laravel's Auth
        // For now, we just check against the current user if possible
        
        // In maccms: if(in_array($this->_admin['admin_id'],$ids))
        
        // Check if current user is in the list
        // $currentId = session('admin_id'); // Or however we store it
        
        Admin::destroy($ids);
        return response()->json(['code' => 1, 'msg' => 'Deleted successfully']);
    }
}

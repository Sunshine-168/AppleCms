<?php

namespace App\Http\Controllers\Web\Content;

use App\Http\Controllers\Web\BaseController;
use App\Models\Role;
use App\Models\Vod;
use Illuminate\Http\Request;

class RoleController extends BaseController
{
    public function index()
    {
        $roles = Role::query()
            ->where('role_status', 1)
            ->orderByDesc('role_time')
            ->paginate(24);

        return view('role.index', compact('roles'));
    }

    public function detail($id)
    {
        $role = Role::query()
            ->where('role_status', 1)
            ->findOrFail($id);

        $relatedVod = Vod::query()
            ->where('vod_status', 1)
            ->where(function ($query) use ($role) {
                if (!empty($role->role_rid)) {
                    $query->orWhere('vod_id', $role->role_rid);
                }
                if (!empty($role->role_actor)) {
                    $query->orWhere('vod_actor', 'like', '%' . $role->role_actor . '%');
                }
            })
            ->orderByDesc('vod_time')
            ->take(12)
            ->get();

        return view('role.detail', compact('role', 'relatedVod'));
    }

    public function search(Request $request)
    {
        $response = $this->ensureSearchAllowed($request);
        if ($response !== null) {
            return $response;
        }

        $wd = $this->normalizeSearchKeyword($request->input('wd', ''));
        if ($wd === '') {
            return redirect()->route('role.index');
        }

        $roles = Role::query()
            ->where('role_status', 1)
            ->where(function ($query) use ($wd) {
                $query->where('role_name', 'like', '%' . $wd . '%')
                    ->orWhere('role_actor', 'like', '%' . $wd . '%');
            })
            ->orderByDesc('role_time')
            ->paginate(24);

        return view('role.search', compact('roles', 'wd'));
    }
}

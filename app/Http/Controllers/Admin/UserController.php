<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UserSaveRequest;
use App\Models\Group;
use App\Models\Plog;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $param = array_merge([
            'status' => '',
            'group' => '',
            'wd' => '',
        ], $request->all());
        $page = max(1, (int) ($param['page'] ?? 1));
        $limit = max(1, (int) ($param['limit'] ?? $this->pagesize));
        $query = User::query()->with('group');

        // Filter by Status
        if ($param['status'] !== '') {
            $query->where('user_status', $param['status']);
        }

        // Filter by Group
        if ($param['group'] !== '') {
            $query->where('group_id', $param['group']);
        }

        // Search by keyword
        if ($param['wd'] !== '') {
            $query->where('user_name', 'like', '%' . $param['wd'] . '%');
        }

        $query->orderBy('user_id', 'desc');
        $total = $query->count();
        $list = $query->skip(($page - 1) * $limit)->take($limit)->get();

        $groups = Group::all();
        $param['page'] = '{page}';
        $param['limit'] = '{limit}';

        return view('admin.user.index', compact('list', 'groups', 'param', 'total', 'page', 'limit'));
    }

    public function reward(Request $request)
    {
        $param = array_merge([
            'uid' => 0,
            'level' => '',
            'wd' => '',
        ], $request->all());
        $param['uid'] = (int) $param['uid'];
        $page = max(1, (int) ($param['page'] ?? 1));
        $limit = max(1, (int) ($param['limit'] ?? $this->pagesize));

        $query = User::query()->with('group');
        if ($param['uid'] > 0) {
            if ($param['level'] === '1') {
                $query->where('user_pid', $param['uid']);
            } elseif ($param['level'] === '2') {
                $query->where('user_pid_2', $param['uid']);
            } elseif ($param['level'] === '3') {
                $query->where('user_pid_3', $param['uid']);
            } else {
                $query->where(function ($subQuery) use ($param) {
                    $subQuery->where('user_pid', $param['uid'])
                        ->orWhere('user_pid_2', $param['uid'])
                        ->orWhere('user_pid_3', $param['uid']);
                });
            }
        }

        if ($param['wd'] !== '') {
            $query->where('user_name', 'like', '%' . trim((string) $param['wd']) . '%');
        }

        $total = $query->count();
        $list = $query->orderBy('user_id', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $uid = $param['uid'];
        $data = [
            'level_cc_1' => $uid > 0 ? User::query()->where('user_pid', $uid)->count() : 0,
            'level_cc_2' => $uid > 0 ? User::query()->where('user_pid_2', $uid)->count() : 0,
            'level_cc_3' => $uid > 0 ? User::query()->where('user_pid_3', $uid)->count() : 0,
            'points_cc_1' => $uid > 0 ? (int) Plog::query()->where('user_id', $uid)->where('plog_type', 4)->sum('plog_points') : 0,
            'points_cc_2' => $uid > 0 ? (int) Plog::query()->where('user_id', $uid)->where('plog_type', 5)->sum('plog_points') : 0,
            'points_cc_3' => $uid > 0 ? (int) Plog::query()->where('user_id', $uid)->where('plog_type', 6)->sum('plog_points') : 0,
        ];

        $param['page'] = '{page}';
        $param['limit'] = '{limit}';

        return view('admin.user.reward', compact('data', 'list', 'total', 'page', 'limit', 'param'));
    }

    public function info(UserSaveRequest $request, $id = null)
    {
        $info = $id ? User::find($id) : new User();
        $groups = Group::all();

        if ($request->isMethod('post')) {
            $data = $request->except(['_token']);

            // Password handling
            if (!empty($data['user_pwd'])) {
                $data['user_pwd'] = md5($data['user_pwd']);
            } else {
                unset($data['user_pwd']);
            }

            // Time handling
            if (isset($data['user_start_time']) && !is_numeric($data['user_start_time'])) {
                $data['user_start_time'] = strtotime($data['user_start_time']);
            }
            if (isset($data['user_end_time']) && !is_numeric($data['user_end_time'])) {
                $data['user_end_time'] = strtotime($data['user_end_time']);
            }

            // Status handling
            $data['user_status'] = (int) $request->input('user_status', 0);

            if ($id) {
                User::where('user_id', $id)->update($data);
            } else {
                if (empty($data['user_pwd'])) {
                    return back()->withErrors(['user_pwd' => 'Password is required']);
                }
                User::create($data);
            }
            
            return redirect()->route('admin.user.index')->with('success', 'Saved successfully');
        }

        return view('admin.user.info', compact('info', 'groups'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) return response()->json(['code' => 1001, 'msg' => 'Param error']);

        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        User::destroy($ids);
        return response()->json(['code' => 1, 'msg' => 'Deleted successfully']);
    }
}

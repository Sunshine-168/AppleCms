<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Web\BaseController;
use App\Models\Art;
use App\Models\Plog;
use App\Models\Ulog;
use App\Models\Vod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UlogController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            if (!Auth::check()) {
                if ($request->ajax()) {
                    return response()->json(['code' => 1003, 'msg' => 'Login required']);
                }
                return redirect()->route('login');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Ulog::where('user_id', $user->user_id);

        if ($request->has('type')) {
            $query->where('ulog_type', $request->type);
        }
        if ($request->has('mid')) {
            $query->where('ulog_mid', $request->mid);
        }

        $logs = $query->orderBy('ulog_time', 'desc')->paginate(20);

        return view('user.ulog', compact('logs'));
    }

    public function save(Request $request)
    {
        $user = Auth::user();
        $mid = $request->input('mid');
        $rid = $request->input('id');
        $type = $request->input('type');
        $sid = $request->input('sid', 0);
        $nid = $request->input('nid', 0);

        if (!$mid || !$rid || !$type) {
            return response()->json(['code' => 1001, 'msg' => 'Missing parameters']);
        }

        $where = [
            'user_id' => $user->user_id,
            'ulog_mid' => $mid,
            'ulog_rid' => $rid,
            'ulog_type' => $type,
            'ulog_sid' => $sid,
            'ulog_nid' => $nid,
        ];

        $log = Ulog::where($where)->first();

        if ($log) {
            $log->ulog_time = time();
            $log->save();
            return response()->json(['code' => 1, 'msg' => 'Log updated']);
        }

        $data = $where;
        $data['ulog_time'] = time();
        $data['ulog_points'] = 0;

        if ($mid == 1 && $type > 3) {
            $vod = Vod::find($rid);
            if ($vod) {
                if ($type == 4) {
                    $data['ulog_points'] = $vod->vod_points_play;
                } elseif ($type == 5) {
                    $data['ulog_points'] = $vod->vod_points_down;
                }
            }
        }

        Ulog::create($data);

        return response()->json(['code' => 1, 'msg' => 'Log saved']);
    }

    public function legacyAjax(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['code' => 1003, 'msg' => 'Login required']);
        }

        if ($request->input('ac') === 'set') {
            return $this->save($request);
        }

        $user = Auth::user();
        $query = Ulog::query()->where('user_id', $user->user_id);

        foreach (['mid' => 'ulog_mid', 'id' => 'ulog_rid', 'type' => 'ulog_type'] as $key => $column) {
            $value = (int) $request->input($key, 0);
            if ($value > 0) {
                $query->where($column, $value);
            }
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(50, (int) $request->input('limit', 10)));
        $paginator = $query->orderByDesc('ulog_time')->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'code' => 1,
            'msg' => 'ok',
            'page' => $paginator->currentPage(),
            'pagecount' => $paginator->lastPage(),
            'limit' => $paginator->perPage(),
            'total' => $paginator->total(),
            'list' => $paginator->items(),
        ]);
    }

    public function buyPopedomLegacy(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['code' => 1003, 'msg' => 'Login required']);
        }

        $user = Auth::user();
        $mid = (string) $request->input('mid', '');
        $type = (string) $request->input('type', '');
        $rid = (int) $request->input('id', 0);
        $sid = (int) $request->input('sid', 0);
        $nid = (int) $request->input('nid', 0);

        if (!in_array($mid, ['1', '2'], true) || !in_array($type, ['1', '4', '5'], true) || $rid < 1) {
            return response()->json(['code' => 2001, 'msg' => '参数错误']);
        }

        $ulogType = (int) $type;
        $ulogData = [
            'user_id' => $user->user_id,
            'ulog_mid' => (int) $mid,
            'ulog_rid' => $rid,
            'ulog_sid' => $sid,
            'ulog_nid' => $nid,
            'ulog_type' => $ulogType,
        ];

        if ($mid === '2') {
            $item = Art::query()->find($rid);
            if (!$item) {
                return response()->json(['code' => 2001, 'msg' => '数据不存在']);
            }
            $pointsColumn = (string) config('maccms.user.art_points_type', '1') === '1' ? 'art_points' : 'art_points_detail';
            if ((string) config('maccms.user.art_points_type', '1') === '1') {
                $ulogData['ulog_sid'] = 0;
                $ulogData['ulog_nid'] = 0;
            }
        } else {
            $item = Vod::query()->find($rid);
            if (!$item) {
                return response()->json(['code' => 2001, 'msg' => '数据不存在']);
            }
            $pointsColumn = (string) config('maccms.user.vod_points_type', '1') === '1'
                ? 'vod_points'
                : ('vod_points_' . ($ulogType === 4 ? 'play' : 'down'));
            if ((string) config('maccms.user.vod_points_type', '1') === '1') {
                $ulogData['ulog_sid'] = 0;
                $ulogData['ulog_nid'] = 0;
            }
        }

        $ulogData['ulog_points'] = (int) ($item->{$pointsColumn} ?? 0);

        $exists = Ulog::query()->where($ulogData)->exists();
        if ($exists) {
            return response()->json(['code' => 1, 'msg' => '您已购买过此权限']);
        }

        if ($ulogData['ulog_points'] > (int) $user->user_points) {
            return response()->json(['code' => 2002, 'msg' => '积分不足']);
        }

        $user->decrement('user_points', $ulogData['ulog_points']);

        Plog::query()->create([
            'user_id' => $user->user_id,
            'plog_type' => 8,
            'plog_points' => $ulogData['ulog_points'],
            'plog_time' => time(),
        ]);

        $user->reward((int) $ulogData['ulog_points']);

        Ulog::query()->create($ulogData + [
            'ulog_time' => time(),
        ]);

        return response()->json(['code' => 1, 'msg' => '购买成功']);
    }

    public function delete(Request $request)
    {
        $user = Auth::user();
        $ids = $request->input('ids');
        $type = $request->input('type');
        $all = $request->input('all');

        if ($all) {
            Ulog::where('user_id', $user->user_id)->where('ulog_type', $type)->delete();
            return response()->json(['code' => 1, 'msg' => 'All logs cleared']);
        }

        if ($ids) {
            $idArray = explode(',', $ids);
            Ulog::where('user_id', $user->user_id)->whereIn('ulog_id', $idArray)->delete();
            return response()->json(['code' => 1, 'msg' => 'Logs deleted']);
        }

        return response()->json(['code' => 1001, 'msg' => 'Missing parameters']);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ulog;
use Illuminate\Http\Request;

class UlogController extends BaseController
{
    public function index(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? $this->pagesize));

        $query = Ulog::query();

        if (!empty($param['mid'])) {
            $query->where('ulog_mid', $param['mid']);
        }
        if (!empty($param['type'])) {
            $query->where('ulog_type', $param['type']);
        }
        if (!empty($param['uid'])) {
            $query->where('user_id', $param['uid']);
        }

        $total = $query->count();
        $list = $query->orderBy('ulog_id', 'desc')
                     ->skip(($param['page'] - 1) * $param['limit'])
                     ->take($param['limit'])
                     ->get();

        return view('admin.ulog.index', compact('list', 'total', 'param'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        $all = $request->input('all');

        if ($all) {
            Ulog::truncate();
            return $this->success('清空成功');
        }

        if (empty($ids)) {
            return $this->error('请选择要删除的数据');
        }

        $idArray = is_array($ids) ? $ids : explode(',', $ids);
        Ulog::whereIn('ulog_id', $idArray)->delete();

        return $this->success('删除成功');
    }
}

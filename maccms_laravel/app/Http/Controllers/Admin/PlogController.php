<?php

namespace App\Http\Controllers\Admin;

use App\Models\Plog;
use Illuminate\Http\Request;

class PlogController extends BaseController
{
    public function index(Request $request)
    {
        $param = array_merge([
            'type' => '',
            'uid' => '',
            'wd' => '',
        ], $request->all());
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? $this->pagesize));

        $query = Plog::query();

        if (!empty($param['type'])) {
            $query->where('plog_type', $param['type']);
        }
        if (!empty($param['uid'])) {
            $query->where('user_id', $param['uid']);
        }

        $total = $query->count();
        $list = $query->with('user')
            ->orderBy('plog_id', 'desc')
            ->skip(($param['page'] - 1) * $param['limit'])
            ->take($param['limit'])
            ->get();

        $page = $param['page'];
        $limit = $param['limit'];
        $param['page'] = '{page}';
        $param['limit'] = '{limit}';

        return view('admin.plog.index', compact('list', 'total', 'page', 'limit', 'param'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        $all = $request->input('all');

        if ($all) {
            Plog::truncate();
            return $this->success('清空成功');
        }

        if (empty($ids)) {
            return $this->error('请选择要删除的数据');
        }

        $idArray = is_array($ids) ? $ids : explode(',', $ids);
        Plog::whereIn('plog_id', $idArray)->delete();

        return $this->success('删除成功');
    }
}

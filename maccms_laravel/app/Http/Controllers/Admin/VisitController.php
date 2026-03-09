<?php

namespace App\Http\Controllers\Admin;

use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends BaseController
{
    public function index(Request $request)
    {
        $param = array_merge([
            'mid' => '',
            'time' => '',
            'uid' => '',
            'wd' => '',
        ], $request->all());
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? $this->pagesize));

        $query = Visit::query();

        if ($param['mid'] !== '') {
            $query->where('visit_mid', $param['mid']);
        }
        if (!empty($param['uid'])) {
            $query->where('user_id', $param['uid']);
        }
        if ($param['time'] !== '') {
            $t = strtotime(date('Y-m-d', strtotime('-' . $param['time'] . ' day')));
            $query->where('visit_time', '>=', $t);
        }
        if (!empty($param['wd'])) {
            $a = $param['wd'];
            if (substr($a, 0, 5) === 'http:') {
                $b = str_replace('http:', 'https:', $a);
            } elseif (substr($a, 0, 5) === 'https') {
                $b = str_replace('https:', 'http:', $a);
            } else {
                $a = 'http://' . $param['wd'];
                $b = 'https://' . $param['wd'];
            }
            $query->where(function($q) use ($a, $b) {
                $q->where('visit_ly', 'like', $a . '%')
                  ->orWhere('visit_ly', 'like', $b . '%');
            });
        }

        $total = $query->count();
        $list = $query->with('user')
            ->orderBy('visit_id', 'desc')
            ->skip(($param['page'] - 1) * $param['limit'])
            ->take($param['limit'])
            ->get();

        $page = $param['page'];
        $limit = $param['limit'];
        $param['page'] = '{page}';
        $param['limit'] = '{limit}';

        return view('admin.visit.index', compact('list', 'total', 'page', 'limit', 'param'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        $all = $request->input('all');

        if ($all) {
            Visit::truncate();
            return $this->success('清空成功');
        }

        if (empty($ids)) {
            return $this->error('请选择要删除的数据');
        }

        $idArray = is_array($ids) ? $ids : explode(',', $ids);
        Visit::whereIn('visit_id', $idArray)->delete();

        return $this->success('删除成功');
    }
}

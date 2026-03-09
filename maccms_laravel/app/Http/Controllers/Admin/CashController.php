<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cash;
use Illuminate\Http\Request;

class CashController extends BaseController
{
    public function index(Request $request)
    {
        $param = array_merge([
            'status' => '',
            'uid' => '',
            'wd' => '',
        ], $request->all());
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? $this->pagesize));

        $query = Cash::query();
        
        if ($param['status'] !== '') {
            $query->where('cash_status', $param['status']);
        }
        
        if (!empty($param['uid'])) {
            $query->where('user_id', $param['uid']);
        }
        
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query->where('cash_bank_no', 'like', '%' . $wd . '%');
        }
        
        $total = $query->count();
        $list = $query->with('user')
            ->orderBy('cash_id', 'desc')
            ->skip(($param['page'] - 1) * $param['limit'])
            ->take($param['limit'])
            ->get();

        $page = $param['page'];
        $limit = $param['limit'];
        $param['page'] = '{page}';
        $param['limit'] = '{limit}';

        return view('admin.cash.index', compact('list', 'total', 'page', 'limit', 'param'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        $all = (int) $request->input('all', 0);

        if (empty($ids) && $all !== 1) {
            return $this->error('请选择要删除的数据');
        }

        $query = Cash::query();
        if ($all === 1) {
            $query->where('cash_id', '>', 0);
        } else {
            $ids = is_array($ids) ? $ids : explode(',', $ids);
            $query->whereIn('cash_id', $ids);
        }

        $items = $query->get();
        foreach ($items as $item) {
            $result = $item->deleteWithRestore();
            if (($result['code'] ?? 1001) !== 1) {
                return $this->error($result['msg'] ?? '删除失败');
            }
        }

        return $this->success('删除成功');
    }

    public function audit(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $this->error('请选择要审核的数据');
        }

        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $items = Cash::query()->whereIn('cash_id', $ids)->get();
        foreach ($items as $item) {
            $result = $item->auditRecord();
            if (($result['code'] ?? 1001) !== 1) {
                return $this->error($result['msg'] ?? '审核失败');
            }
        }

        return $this->success('审核成功');
    }
}

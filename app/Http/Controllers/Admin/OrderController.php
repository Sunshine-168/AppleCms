<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends BaseController
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

        $query = Order::query();
        
        if ($param['status'] !== '') {
            $query->where('order_status', $param['status']);
        }
        
        if (!empty($param['uid'])) {
            $query->where('user_id', $param['uid']);
        }
        
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query->where('order_code', 'like', '%' . $wd . '%');
        }
        
        $total = $query->count();
        $list = $query->with('user')
            ->orderBy('order_id', 'desc')
            ->skip(($param['page'] - 1) * $param['limit'])
            ->take($param['limit'])
            ->get();
        $page = $param['page'];
        $limit = $param['limit'];
        $param['page'] = '{page}';
        $param['limit'] = '{limit}';

        return view('admin.order.index', compact('list', 'total', 'page', 'limit', 'param'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        $all = $request->input('all');
        
        if (empty($ids) && empty($all)) {
            return $this->error('请选择要删除的数据');
        }
        
        $query = Order::query();
        
        if (!empty($ids)) {
            $ids = is_array($ids) ? $ids : explode(',', $ids);
            $query->whereIn('order_id', $ids);
        } elseif ($all == 1) {
            // Delete all
        }
        
        $query->delete();

        return $this->success('删除成功');
    }
}

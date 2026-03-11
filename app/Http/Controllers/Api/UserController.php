<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserDetailRequest;
use App\Http\Requests\Api\UserListRequest;
use App\Models\User;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList(UserListRequest $request)
    {
        $param = $request->validated();
        $offset = intval($param['offset'] ?? 0);
        $limit = intval($param['limit'] ?? 20);

        $query = User::query()->where('user_status', 1);

        if (isset($param['id']) && $param['id'] !== '') {
            $query->where('user_id', intval($param['id']));
        }
        if (!empty($param['user_name'])) {
            $query->where('user_name', 'like', '%' . $this->formatSqlString($param['user_name']) . '%');
        }
        if (!empty($param['group_id'])) {
            $query->where('group_id', intval($param['group_id']));
        }

        $total = $query->count();
        $by = $param['orderby'] ?? 'time';
        if (!in_array($by, ['id', 'time', 'login_time', 'points'])) {
            $by = 'time';
        }

        $rows = $query->orderByRaw('user_' . $by . ' DESC')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $this->response(1, '获取成功', compact('offset', 'limit', 'total', 'rows'));
    }

    public function getDetail(UserDetailRequest $request)
    {
        $id = (int) $request->validated()['id'];

        $info = User::query()->where('user_id', $id)->where('user_status', 1)->first();
        if (!$info) {
            return $this->response(1002, '数据不存在');
        }

        return $this->response(1, '获取成功', $info->toArray());
    }
}

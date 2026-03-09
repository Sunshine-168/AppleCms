<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CommentListRequest;
use App\Models\Comment;

class CommentController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList(CommentListRequest $request)
    {
        $param = $request->validated();
        $offset = intval($param['offset'] ?? 0);
        $limit = intval($param['limit'] ?? 20);

        $query = Comment::query()->where('comment_status', 1);

        foreach (['rid' => 'comment_rid', 'pid' => 'comment_pid', 'mid' => 'comment_mid', 'uid' => 'comment_uid'] as $key => $column) {
            if (isset($param[$key]) && $param[$key] !== '') {
                $query->where($column, intval($param[$key]));
            }
        }

        $total = $query->count();
        $by = $param['orderby'] ?? 'time';
        if (!in_array($by, ['id', 'time', 'up', 'down'])) {
            $by = 'time';
        }

        $rows = $query->orderByRaw('comment_' . $by . ' DESC')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $this->response(1, '获取成功', compact('offset', 'limit', 'total', 'rows'));
    }
}

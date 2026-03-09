<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\TopicDetailRequest;
use App\Http\Requests\Api\TopicListRequest;
use App\Models\Topic;

class TopicController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList(TopicListRequest $request)
    {
        $param = $request->validated();
        $offset = intval($param['offset'] ?? 0);
        $limit = intval($param['limit'] ?? 20);

        $query = Topic::query()->where('topic_status', 1);

        if (isset($param['id']) && $param['id'] !== '') {
            $query->where('topic_id', intval($param['id']));
        }
        if (!empty($param['topic_name'])) {
            $query->where('topic_name', 'like', '%' . $this->formatSqlString($param['topic_name']) . '%');
        }
        if (!empty($param['topic_tag'])) {
            $query->where('topic_tag', 'like', '%' . $this->formatSqlString($param['topic_tag']) . '%');
        }

        $total = $query->count();
        $by = $param['orderby'] ?? 'time';
        if (!in_array($by, ['id', 'time', 'hits', 'hits_day', 'hits_week', 'hits_month', 'sort'])) {
            $by = 'time';
        }

        $rows = $query->orderByRaw('topic_' . $by . ' DESC')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $this->response(1, '获取成功', compact('offset', 'limit', 'total', 'rows'));
    }

    public function getDetail(TopicDetailRequest $request)
    {
        $id = (int) $request->validated()['id'];

        $info = Topic::query()
            ->where('topic_id', $id)
            ->where('topic_status', 1)
            ->first();

        if (!$info) {
            return $this->response(1002, '数据不存在');
        }

        return $this->response(1, '获取成功', $info->toArray());
    }
}

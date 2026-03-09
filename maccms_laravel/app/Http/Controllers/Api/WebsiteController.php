<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\WebsiteDetailRequest;
use App\Http\Requests\Api\WebsiteListRequest;
use App\Models\Website;

class WebsiteController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList(WebsiteListRequest $request)
    {
        $param = $request->validated();
        $offset = intval($param['offset'] ?? 0);
        $limit = intval($param['limit'] ?? 20);

        $query = Website::query()->where('website_status', 1);

        if (isset($param['id']) && $param['id'] !== '') {
            $query->where('website_id', intval($param['id']));
        }
        if (!empty($param['website_name'])) {
            $query->where('website_name', 'like', '%' . $this->formatSqlString($param['website_name']) . '%');
        }
        if (!empty($param['website_letter'])) {
            $query->where('website_letter', $this->formatSqlString($param['website_letter']));
        }

        $total = $query->count();
        $by = $param['orderby'] ?? 'time';
        if (!in_array($by, ['id', 'time', 'hits', 'hits_day', 'hits_week', 'hits_month', 'sort'])) {
            $by = 'time';
        }

        $rows = $query->orderByRaw('website_' . $by . ' DESC')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $this->response(1, '获取成功', compact('offset', 'limit', 'total', 'rows'));
    }

    public function getDetail(WebsiteDetailRequest $request)
    {
        $id = (int) $request->validated()['id'];

        $info = Website::query()
            ->where('website_id', $id)
            ->where('website_status', 1)
            ->first();

        if (!$info) {
            return $this->response(1002, '数据不存在');
        }

        return $this->response(1, '获取成功', $info->toArray());
    }
}

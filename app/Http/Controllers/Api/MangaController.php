<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\MangaDetailRequest;
use App\Http\Requests\Api\MangaListRequest;
use App\Models\Manga;

class MangaController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList(MangaListRequest $request)
    {
        $param = $request->validated();
        $offset = intval($param['offset'] ?? 0);
        $limit = intval($param['limit'] ?? 20);

        $query = Manga::query()->where('manga_status', 1);

        if (isset($param['type_id'])) {
            $query->where('type_id', intval($param['type_id']));
        }
        if (isset($param['id'])) {
            $query->where('manga_id', intval($param['id']));
        }
        foreach (['manga_letter', 'manga_area', 'manga_year'] as $field) {
            if (!empty($param[$field])) {
                $query->where($field, $this->formatSqlString($param[$field]));
            }
        }
        foreach (['manga_tag', 'manga_name'] as $field) {
            if (!empty($param[$field])) {
                $query->where($field, 'like', '%' . $this->formatSqlString($param[$field]) . '%');
            }
        }

        $total = $query->count();
        $by = $param['orderby'] ?? 'time';
        if (!in_array($by, ['id', 'time', 'hits', 'hits_day', 'hits_week', 'hits_month', 'score'])) {
            $by = 'time';
        }

        $rows = $query->orderByRaw('manga_' . $by . ' DESC')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $this->response(1, '获取成功', compact('offset', 'limit', 'total', 'rows'));
    }

    public function getDetail(MangaDetailRequest $request)
    {
        $id = (int) $request->validated()['id'];

        $info = Manga::query()
            ->where('manga_id', $id)
            ->where('manga_status', 1)
            ->first();

        if (!$info) {
            return $this->response(1002, '数据不存在');
        }

        return $this->response(1, '获取成功', $info->toArray());
    }
}

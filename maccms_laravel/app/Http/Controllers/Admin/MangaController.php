<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\MangaSaveRequest;
use App\Models\Manga;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MangaController extends BaseController
{
    public function index(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? $this->pagesize));

        $query = Manga::query();

        if (!empty($param['type'])) {
            $query->where(function($q) use ($param) {
                $q->where('type_id', $param['type'])
                  ->orWhere('type_id_1', $param['type']);
            });
        }
        if (!empty($param['level'])) {
            $query->where('manga_level', $param['level']);
        }
        if (isset($param['status']) && in_array($param['status'], ['0', '1'])) {
            $query->where('manga_status', $param['status']);
        }
        if (!empty($param['lock'])) {
            $query->where('manga_lock', $param['lock']);
        }
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query->where('manga_name', 'like', '%' . $wd . '%');
        }

        $total = $query->count();
        $list = $query->orderBy('manga_time', 'desc')
                     ->skip(($param['page'] - 1) * $param['limit'])
                     ->take($param['limit'])
                     ->get();

        return view('admin.manga.index', compact('list', 'total', 'param'));
    }

    public function info(MangaSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            if ($id) {
                $manga = Manga::findOrFail($id);
                $manga->update($data);
                return $this->success('更新成功');
            } else {
                Manga::create($data);
                return $this->success('添加成功');
            }
        }

        $manga = $id ? Manga::findOrFail($id) : new Manga();
        $types = Type::where('type_mid', 3)->get();

        return view('admin.manga.info', compact('manga', 'types'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $this->error('请选择要删除的数据');
        }

        $idArray = is_array($ids) ? $ids : explode(',', $ids);
        Manga::whereIn('manga_id', $idArray)->delete();

        return $this->success('删除成功');
    }
}

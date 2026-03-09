<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LinkSaveRequest;
use App\Models\Link;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function index(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? 20));

        $query = Link::query();
        
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query->where('link_name', 'like', '%' . $wd . '%');
        }
        
        $links = $query->orderBy('link_id', 'desc')
                      ->paginate($param['limit'], ['*'], 'page', $param['page']);
        
        return view('admin.link.index', compact('links', 'param'));
    }

    public function info(LinkSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            if (!empty($id)) {
                $link = Link::findOrFail($id);
                $link->update($data);
            } else {
                $link = Link::create($data);
            }
            
            return redirect()->route('admin.link.index')->with('success', '保存成功');
        }
        
        $link = $id ? Link::findOrFail($id) : new Link();
        
        return view('admin.link.info', compact('link'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->withErrors(['msg' => '请选择要删除的数据']);
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        Link::whereIn('link_id', $ids)->delete();
        
        return back()->with('success', '删除成功');
    }

    public function batch(Request $request)
    {
        $ids = $request->input('ids', []);
        $linkNames = $request->input('link_name', []);
        $linkSorts = $request->input('link_sort', []);
        $linkUrls = $request->input('link_url', []);
        $linkTypes = $request->input('link_type', []);
        $linkLogos = $request->input('link_logo', []);
        
        foreach ($ids as $k => $id) {
            $data = [
                'link_id' => intval($id),
                'link_name' => $linkNames[$k] ?? '未知',
                'link_sort' => intval($linkSorts[$k] ?? 0),
                'link_url' => $linkUrls[$k] ?? '',
                'link_type' => intval($linkTypes[$k] ?? 0),
                'link_logo' => $linkLogos[$k] ?? '',
            ];
            
            if (empty($data['link_name'])) {
                $data['link_name'] = '未知';
            }
            
            $link = Link::find($data['link_id']);
            if ($link) {
                $link->update($data);
            }
        }
        
        return back()->with('success', '批量更新成功');
    }
}

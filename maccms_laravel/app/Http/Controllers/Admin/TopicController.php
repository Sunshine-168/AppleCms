<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TopicSaveRequest;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? 20));

        $query = Topic::query();
        
        if (isset($param['status']) && in_array($param['status'], ['0', '1'], true)) {
            $query->where('topic_status', $param['status']);
        }
        
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query->where('topic_name', 'like', '%' . $wd . '%');
        }
        
        $topics = $query->orderBy('topic_time', 'desc')
                       ->paginate($param['limit'], ['*'], 'page', $param['page']);
        
        // Check if needs to make static pages
        foreach ($topics as $topic) {
            $topic->ismake = 1;
            $config = config('maccms.view');
            if ($config['topic_detail'] > 0 && $topic->topic_time_make < $topic->topic_time) {
                $topic->ismake = 0;
            }
        }
        
        return view('admin.topic.index', compact('topics', 'param'));
    }

    public function info(TopicSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            if (!empty($id)) {
                $topic = Topic::findOrFail($id);
                $topic->update($data);
            } else {
                $topic = Topic::create($data);
            }
            
            return redirect()->route('admin.topic.index')->with('success', '保存成功');
        }
        
        $topic = $id ? Topic::findOrFail($id) : new Topic();
        $config = config('maccms.site');
        
        return view('admin.topic.info', compact('topic', 'config'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->withErrors(['msg' => '请选择要删除的数据']);
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        Topic::whereIn('topic_id', $ids)->delete();
        
        return back()->with('success', '删除成功');
    }

    public function field(Request $request)
    {
        $ids = $request->input('ids');
        $col = $request->input('col');
        $val = $request->input('val');
        
        if (empty($ids) || !in_array($col, ['topic_status', 'topic_level'])) {
            return back()->withErrors(['msg' => '参数错误']);
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        Topic::whereIn('topic_id', $ids)->update([$col => $val]);
        
        return back()->with('success', '操作成功');
    }
}

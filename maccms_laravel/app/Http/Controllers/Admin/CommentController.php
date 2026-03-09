<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommentSaveRequest;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? 20));

        $query = Comment::query();
        
        if (isset($param['status']) && in_array($param['status'], ['0', '1'], true)) {
            $query->where('comment_status', $param['status']);
        }
        
        if (isset($param['mid']) && in_array($param['mid'], ['1', '2', '3'])) {
            $query->where('comment_mid', $param['mid']);
        }
        
        if (!empty($param['uid'])) {
            $query->where('user_id', $param['uid']);
        }
        
        if (!empty($param['report'])) {
            if ($param['report'] == 1) {
                $query->where('comment_report', 0);
            } else {
                $query->where('comment_report', '>', 0);
            }
        }
        
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query->where(function($q) use ($wd) {
                $q->where('comment_name', 'like', '%' . $wd . '%')
                  ->orWhere('comment_content', 'like', '%' . $wd . '%');
            });
        }
        
        $comments = $query->orderBy('comment_id', 'desc')
                         ->paginate($param['limit'], ['*'], 'page', $param['page']);
        
        return view('admin.comment.index', compact('comments', 'param'));
    }

    public function info(CommentSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            if (!empty($id)) {
                $comment = Comment::findOrFail($id);
                $comment->update($data);
            } else {
                $comment = Comment::create($data);
            }
            
            return redirect()->route('admin.comment.index')->with('success', '保存成功');
        }
        
        $comment = $id ? Comment::findOrFail($id) : new Comment();
        
        return view('admin.comment.info', compact('comment'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        $all = $request->input('all');
        
        if (empty($ids) && empty($all)) {
            return back()->withErrors(['msg' => '请选择要删除的数据']);
        }
        
        $query = Comment::query();
        
        if (!empty($ids)) {
            $ids = is_array($ids) ? $ids : explode(',', $ids);
            $query->whereIn('comment_id', $ids);
        } elseif ($all == 1) {
            // Delete all
        }
        
        $query->delete();
        
        return back()->with('success', '删除成功');
    }

    public function field(Request $request)
    {
        $ids = $request->input('ids');
        $col = $request->input('col');
        $val = $request->input('val');
        
        if (empty($ids) || !in_array($col, ['comment_status', 'comment_report'])) {
            return back()->withErrors(['msg' => '参数错误']);
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        Comment::whereIn('comment_id', $ids)->update([$col => $val]);
        
        return back()->with('success', '操作成功');
    }
}

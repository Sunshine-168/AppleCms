<?php

namespace App\Http\Controllers\Web\Content;

use App\Http\Controllers\Web\BaseController;
use App\Http\Requests\Web\CommentSaveRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        if (config('maccms.comment.status') == 0) {
            abort(403, 'Comment is closed');
        }
    }

    public function index(Request $request)
    {
        $query = Comment::where('comment_status', 1);

        if ($request->has('mid')) {
            $query->where('comment_mid', $request->mid);
        }
        if ($request->has('rid')) {
            $query->where('comment_rid', $request->rid);
        }

        $comments = $query->orderBy('comment_time', 'desc')->paginate(10);
        
        if ($request->ajax()) {
            return view('comment.ajax', compact('comments'));
        }

        return view('comment.index', compact('comments'));
    }

    public function save(CommentSaveRequest $request)
    {
        if (config('maccms.comment.login') == 1 && !Auth::check()) {
            return response()->json(['code' => 1003, 'msg' => 'Login required']);
        }

        $timespan = config('maccms.comment.timespan', 3);
        if (!$this->checkFrequency('comment_timespan', $timespan)) {
            return response()->json(['code' => 1005, 'msg' => 'Too frequent']);
        }

        $userInfo = $this->getUserInfo();
        $data = $request->only(['comment_content', 'comment_mid', 'comment_rid', 'comment_pid']);
        $data['comment_content'] = $this->filterWords($data['comment_content']);
        $data['user_id'] = $userInfo['user_id'];
        $data['comment_name'] = $userInfo['name'];
        $data['comment_ip'] = ip2long($request->ip());
        $data['comment_time'] = time();
        $data['comment_status'] = config('maccms.comment.audit') == 1 ? 0 : 1;

        $comment = Comment::create($data);

        $msg = $data['comment_status'] == 0 
            ? 'Comment submitted, waiting for audit' 
            : 'Comment successful';

        return response()->json(['code' => 1, 'msg' => $msg, 'data' => $comment]);
    }

    public function report(Request $request)
    {
        $id = (int) $request->input('id', 0);
        if ($id < 1) {
            return response()->json(['code' => 1001, 'msg' => '参数错误']);
        }

        $cookie = 'comment-report-' . $id;
        if ($request->cookie($cookie)) {
            return response()->json(['code' => 1002, 'msg' => '您已操作过']);
        }

        Comment::query()->where('comment_id', $id)->increment('comment_report');

        return response()
            ->json(['code' => 1, 'msg' => '操作成功'])
            ->cookie($cookie, 't', (int) config('maccms.comment.timespan', 3));
    }
}

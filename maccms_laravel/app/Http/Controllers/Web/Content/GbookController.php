<?php

namespace App\Http\Controllers\Web\Content;

use App\Http\Controllers\Web\BaseController;
use App\Http\Requests\Web\GbookSaveRequest;
use App\Models\Gbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GbookController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        if (config('maccms.gbook.status') == 0) {
            abort(403, 'Gbook is closed');
        }
    }

    public function index(Request $request)
    {
        $gbooks = Gbook::where('gbook_status', 1)
                      ->orderBy('gbook_time', 'desc')
                      ->paginate(10);
        
        return view('gbook.index', compact('gbooks'));
    }

    public function report(Request $request)
    {
        return view('gbook.report', [
            'id' => (int) $request->input('id', 0),
            'name' => (string) $request->input('name', ''),
        ]);
    }

    public function save(GbookSaveRequest $request)
    {
        if (config('maccms.gbook.login') == 1 && !Auth::check()) {
            if ($request->ajax()) {
                return response()->json(['code' => 1003, 'msg' => 'Login required']);
            }
            return back()->with('error', 'Login required');
        }

        $timespan = config('maccms.gbook.timespan', 3);
        if (!$this->checkFrequency('gbook_timespan', $timespan)) {
            if ($request->ajax()) {
                return response()->json(['code' => 1005, 'msg' => 'Too frequent']);
            }
            return back()->with('error', 'Too frequent');
        }

        $userInfo = $this->getUserInfo();
        $data = $request->only(['gbook_content']);
        $data['gbook_content'] = $this->filterWords($data['gbook_content']);
        $data['user_id'] = $userInfo['user_id'];
        $data['gbook_name'] = $userInfo['name'];
        $data['gbook_ip'] = ip2long($request->ip());
        $data['gbook_time'] = time();
        $data['gbook_status'] = config('maccms.gbook.audit') == 1 ? 0 : 1;
        $data['gbook_reply'] = '';

        Gbook::create($data);

        $msg = $data['gbook_status'] == 0 
            ? 'Message submitted, waiting for audit' 
            : 'Message submitted successfully';

        if ($request->ajax()) {
            return response()->json(['code' => 1, 'msg' => $msg]);
        }

        return back()->with('success', $msg);
    }
}

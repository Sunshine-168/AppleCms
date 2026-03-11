<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function ajaxLogin()
    {
        if (Auth::check()) {
            return $this->ajaxInfo();
        }

        return view('user.ajax_login');
    }

    public function ajaxInfo()
    {
        if (!Auth::check()) {
            return view('user.ajax_login');
        }

        $user = Auth::user();
        $user->normalizeMembership();
        $user->queueLegacyCookies();
        $user->loadMissing('group');

        return view('user.ajax_info', ['user' => $user]);
    }

    public function login()
    {
        if (Auth::check()) {
            return redirect('/user/index');
        }
        return view('user.login');
    }

    public function loginPost(Request $request)
    {
        $request->validate([
            'user_name' => 'required',
            'user_pwd' => 'required',
        ]);

        if ((string) config('maccms.user.login_verify', '0') === '1') {
            $verify = trim((string) $request->input('verify', ''));
            $saved = (string) Session::get('verify_code_default', '');
            if ($verify === '' || strcasecmp($saved, $verify) !== 0) {
                if ($request->ajax()) {
                    return response()->json(['code' => 1002, 'msg' => __('verify_err')]);
                }

                return back()->withErrors(['msg' => __('verify_err')])->withInput();
            }
        }

        $loginName = trim((string) $request->input('user_name'));
        $user = User::query()
            ->where(function ($query) use ($loginName) {
                $query->where('user_name', $loginName)
                    ->orWhere('user_email', $loginName);
            })
            ->first();

        if ($user && $user->user_pwd === md5($request->input('user_pwd'))) {
            if ($user->user_status == 0) {
                if ($request->ajax()) {
                    return response()->json(['code' => 1002, 'msg' => 'Account is disabled']);
                }
                return back()->withErrors(['msg' => 'Account is disabled']);
            }

            $user->normalizeMembership();
            Auth::login($user);
            $user->refreshLoginMeta($request->ip());
            $user->refresh()->queueLegacyCookies();

            if ($request->ajax()) {
                return response()->json(['code' => 1, 'msg' => 'Login successful']);
            }

            return redirect('/user/index');
        }

        if ($request->ajax()) {
            return response()->json(['code' => 1001, 'msg' => 'Invalid credentials']);
        }

        return back()->withErrors(['msg' => 'Invalid credentials']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        User::forgetLegacyCookies();

        if ($request->ajax()) {
            return response()->json(['code' => 1, 'msg' => 'Logout successful']);
        }

        return redirect('/user/login');
    }
}

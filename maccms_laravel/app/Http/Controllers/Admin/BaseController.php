<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class BaseController extends Controller
{
    protected $admin;
    protected $pagesize = 20;
    protected $makesize = 100;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // 检查登录状态（除了登录页面）
            if (!$this->isLoginPage($request) && !$this->checkLogin()) {
                if ($request->ajax()) {
                    return response()->json(['code' => 1001, 'msg' => '请先登录']);
                }
                return redirect()->route('admin.login');
            }

            if ($this->admin) {
                // 检查权限
                if (!$this->checkAuth($request)) {
                    if ($request->ajax()) {
                        return response()->json(['code' => 1002, 'msg' => '权限不足']);
                    }
                    abort(403, '权限不足');
                }

                View::share('admin', $this->admin);
                View::share('pagesize', $this->pagesize);
                View::share('makesize', $this->makesize);
            }

            return $next($request);
        });
    }

    protected function isLoginPage($request)
    {
        $route = $request->route()->getName();
        return $route === 'admin.login';
    }

    protected function checkLogin()
    {
        $adminId = Session::get('admin_id');
        if (!$adminId) {
            return false;
        }

        $admin = Admin::find($adminId);
        if (!$admin || $admin->admin_status == 0) {
            Session::forget('admin_id');
            Session::forget('admin_name');
            return false;
        }

        $this->admin = $admin;
        return true;
    }

    protected function checkAuth($request)
    {
        if (!$this->admin) {
            return false;
        }

        // 超级管理员拥有所有权限
        if ($this->admin->admin_id == 1) {
            return true;
        }

        $controller = class_basename($request->route()->getController());
        $controller = str_replace('Controller', '', $controller);
        $action = $request->route()->getActionMethod();

        $auths = $this->admin->admin_auth . ',index/index,index/welcome,index/logout,';
        $cur = ',' . strtolower($controller) . '/' . strtolower($action) . ',';

        return strpos($auths, $cur) !== false;
    }

    protected function success($msg = '操作成功', $data = [], $url = '')
    {
        if (request()->ajax()) {
            return response()->json([
                'code' => 1,
                'msg' => $msg,
                'data' => $data,
                'url' => $url
            ]);
        }

        if ($url) {
            return redirect($url)->with('success', $msg);
        }

        return back()->with('success', $msg);
    }

    protected function error($msg = '操作失败', $data = [], $url = '')
    {
        if (request()->ajax()) {
            return response()->json([
                'code' => 1001,
                'msg' => $msg,
                'data' => $data,
                'url' => $url
            ]);
        }

        if ($url) {
            return redirect($url)->with('error', $msg);
        }

        return back()->with('error', $msg);
    }

    protected function clearCache()
    {
        // 清理缓存
        Cache::flush();
        
        // 清理运行时缓存
        $cachePath = storage_path('framework/cache');
        $logPath = storage_path('logs');
        $tempPath = storage_path('framework/sessions');

        if (is_dir($cachePath)) {
            $this->deleteDirectory($cachePath);
        }

        return true;
    }

    protected function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }
}

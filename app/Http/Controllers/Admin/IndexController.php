<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class IndexController extends Controller
{
    public function login(LoginRequest $request)
    {
        if ($request->isMethod('post')) {
            $name = $request->input('admin_name');
            $pwd = $request->input('admin_pwd');

            // Custom MD5 check
            $admin = Admin::where('admin_name', $name)->first();

            $passOk = false;
            if ($admin) {
                if (!empty($admin->admin_random)) {
                    $passOk = $admin->admin_pwd === md5($pwd . $admin->admin_random);
                } else {
                    $passOk = $admin->admin_pwd === md5($pwd);
                }
            }

            if ($passOk) {
                if ($admin->admin_status == 0) {
                    return back()->withErrors(['msg' => 'Account disabled']);
                }

                // Login success
                Session::put('admin_id', $admin->admin_id);
                Session::put('admin_name', $admin->admin_name);
                
                // Update login info
                $admin->admin_login_time = time();
                $admin->admin_login_ip = ip2long($request->ip());
                $admin->admin_login_num = $admin->admin_login_num + 1;
                $admin->save();

                return redirect()->route('admin.index');
            }

            return back()->withErrors(['msg' => 'Invalid credentials']);
        }

        // Addon logic: adminloginbg
        $background = '';
        $addonPath = base_path('addons/adminloginbg');
        
        if (file_exists($addonPath . '/info.ini')) {
            $info = parse_ini_file($addonPath . '/info.ini');
            
            // Check if enabled (state=1)
            if ($info && isset($info['state']) && $info['state'] == 1) {
                if (file_exists($addonPath . '/config.php')) {
                    $config_items = include $addonPath . '/config.php';
                    
                    // Parse config array to key-value
                    $config = [];
                    if (is_array($config_items)) {
                        foreach ($config_items as $item) {
                            if (isset($item['name']) && isset($item['value'])) {
                                $config[$item['name']] = $item['value'];
                            }
                        }
                    }

                    if (isset($config['mode'])) {
                        if ($config['mode'] == 'random' || $config['mode'] == 'daily') {
                            $index = $config['mode'] == 'random' ? mt_rand(1, 4000) : date("Ymd") % 4000;
                            $background = "http://img.infinitynewtab.com/wallpaper/" . $index . ".jpg";
                        } else {
                            $background = isset($config['image']) ? asset($config['image']) : '';
                            // If image is relative path, prepend asset url or similar. 
                            // maccms stores uploads in 'upload/...' which is in public root usually.
                        }
                    }
                }
            }
        }

        return view('admin.login', compact('background'));
    }

    public function logout()
    {
        Session::forget('admin_id');
        Session::forget('admin_name');
        return redirect()->route('admin.login');
    }

    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('admin.login');
        }
        
        $admin = Admin::find(Session::get('admin_id'));
        $menus = $this->buildMenus($admin);
        
        return view('admin.index.index', compact('menus', 'admin'));
    }

    public function welcome()
    {
        return view('admin.index.welcome');
    }

    public function quickmenu()
    {
        return view('admin.index.quickmenu');
    }

    public function unlocked(Request $request)
    {
        $password = trim((string) $request->input('password', ''));
        if ($password === '') {
            return response()->json(['code' => 0, 'msg' => __('param_err')]);
        }

        $adminId = Session::get('admin_id');
        if (empty($adminId)) {
            return response()->json(['code' => 0, 'msg' => '登录状态已失效，请重新登录']);
        }

        $admin = Admin::find($adminId);
        if (!$admin) {
            return response()->json(['code' => 0, 'msg' => '登录状态已失效，请重新登录']);
        }

        $passOk = false;
        if (!empty($admin->admin_random)) {
            $passOk = $admin->admin_pwd === md5($password . $admin->admin_random);
        } else {
            $passOk = $admin->admin_pwd === md5($password);
        }

        if (!$passOk) {
            return response()->json(['code' => 0, 'msg' => __('admin/index/pass_err')]);
        }

        return response()->json(['code' => 1, 'msg' => __('admin/index/unlock_ok')]);
    }

    public function clear()
    {
        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        
        if (request()->ajax()) {
            return response()->json(['code' => 1, 'msg' => '缓存清理成功']);
        }
        
        return redirect()->route('admin.index')->with('success', '缓存清理成功');
    }

    protected function buildMenus($admin)
    {
        $menus = config('admin_auth', []);
        $adminAuth = $admin->admin_auth ?? '';
        $authArray = explode(',', $adminAuth);
        
        foreach ($menus as $k1 => &$v1) {
            foreach ($v1['sub'] as $k2 => $v2) {
                if ($v2['show'] == 1) {
                    // 检查权限
                    $controller = strtolower($v2['controller']);
                    $action = strtolower($v2['action']);
                    $authKey = $controller . '/' . $action;
                    
                    // 超级管理员或拥有权限
                    if ($admin->admin_id == 1 || in_array($authKey, $authArray) || in_array('index/welcome', $authArray)) {
                        // 生成URL
                        if (strpos($v2['action'], 'javascript') !== false) {
                            $url = $v2['action'];
                        } else {
                            $routeName = 'admin.' . $controller . '.' . $action;
                            if (!Route::has($routeName) && $action === 'data') {
                                $routeNameIndex = 'admin.' . $controller . '.index';
                                if (Route::has($routeNameIndex)) {
                                    $routeName = $routeNameIndex;
                                }
                            }
                            $url = Route::has($routeName) ? route($routeName) : url('/admin/' . $controller . '/' . $action);
                        }
                        
                        if (!empty($v2['param'])) {
                            $url .= '?' . $v2['param'];
                        }
                        
                        $v1['sub'][$k2]['url'] = $url;
                    } else {
                        unset($v1['sub'][$k2]);
                    }
                } else {
                    unset($v1['sub'][$k2]);
                }
            }
            
            if (empty($v1['sub'])) {
                unset($menus[$k1]);
            }
        }
        
        return $menus;
    }
}

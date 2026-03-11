<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class InstallController extends Controller
{
    /**
     * 安装首页 - 步骤1：协议和语言选择
     */
    public function index(Request $request)
    {
        // 检查是否已安装
        if ($this->isInstalled()) {
            return redirect('/admin');
        }

        $lang = $request->get('lang', 'zh-cn');
        session(['install_lang' => $lang]);

        // 获取可用语言列表
        $langs = ['zh-cn' => '简体中文', 'en' => 'English'];

        return view('install.index', [
            'lang' => $lang,
            'langs' => $langs,
        ]);
    }

    /**
     * 步骤2：环境检测
     */
    public function step2()
    {
        if ($this->isInstalled()) {
            return redirect('/admin');
        }

        $data = [
            'env' => $this->checkEnv(),
            'dir' => $this->checkDir(),
            'func' => $this->checkFunc(),
        ];

        return view('install.step2', ['data' => $data]);
    }

    /**
     * 步骤3：数据库配置
     */
    public function step3()
    {
        if ($this->isInstalled()) {
            return redirect('/admin');
        }

        $installDir = str_replace('/install', '', request()->getPathInfo());
        $installDir = $installDir ?: '/';

        return view('install.step3', ['install_dir' => $installDir]);
    }

    /**
     * 步骤4：测试数据库连接
     */
    public function step4(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/admin');
        }

        if (!$request->isMethod('post')) {
            return response()->json(['code' => 0, 'msg' => __('install.access_denied')]);
        }

        $request->validate([
            'hostname' => 'required',
            'hostport' => 'required|numeric',
            'database' => 'required',
            'username' => 'required',
            'password' => 'required',
            'prefix' => 'required|regex:/^[a-z0-9]{1,20}_$/',
            'cover' => 'required|in:0,1',
        ], [
            'hostname.required' => __('install.server_address') . __('install.required'),
            'hostport.required' => __('install.database_port') . __('install.required'),
            'database.required' => __('install.database_name') . __('install.required'),
            'username.required' => __('install.database_username') . __('install.required'),
            'prefix.required' => __('install.database_pre') . __('install.required'),
            'prefix.regex' => __('install.database_pre') . __('install.format_error'),
        ]);

        $data = $request->only(['hostname', 'hostport', 'database', 'username', 'password', 'prefix']);
        $cover = $request->input('cover', 0);

        // 测试数据库连接
        try {
            $config = [
                'driver' => 'mysql',
                'host' => $data['hostname'],
                'port' => $data['hostport'],
                'database' => $data['database'],
                'username' => $data['username'],
                'password' => $data['password'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => $data['prefix'],
            ];

            // 临时连接测试
            DB::purge('mysql');
            config(['database.connections.test' => $config]);
            DB::connection('test')->select('SELECT 1');

            // 保存配置到 session
            session(['install_db_config' => $config]);

            // 检查数据库是否存在
            if (!$cover) {
                $exists = DB::connection('test')
                    ->select("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?", [$data['database']]);
                if (!empty($exists)) {
                    return response()->json(['code' => 1, 'msg' => __('install.database_name_haved')]);
                }
            }

            // 创建数据库
            DB::connection('test')->statement("CREATE DATABASE IF NOT EXISTS `{$data['database']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            return response()->json(['code' => 1, 'msg' => __('install.database_connect_ok')]);
        } catch (\Exception $e) {
            return response()->json(['code' => 0, 'msg' => __('install.database_connect_err') . ': ' . $e->getMessage()]);
        }
    }

    /**
     * 步骤5：执行安装
     */
    public function step5(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/admin');
        }

        if (!$request->isMethod('post')) {
            return response()->json(['code' => 0, 'msg' => __('install.access_denied')]);
        }

        $request->validate([
            'account' => 'required|alpha_num',
            'password' => 'required|min:6|max:20',
            'install_dir' => 'nullable',
            'initdata' => 'nullable|in:0,1',
        ], [
            'account.required' => __('install.admin_name') . __('install.required'),
            'password.required' => __('install.admin_pass') . __('install.required'),
            'password.min' => __('install.admin_pass') . __('install.min_length'),
        ]);

        $account = $request->input('account');
        $password = $request->input('password');
        $installDir = $request->input('install_dir', '/');
        $initdata = $request->input('initdata', '0');

        $dbConfig = session('install_db_config');
        if (empty($dbConfig)) {
            return response()->json(['code' => 0, 'msg' => __('install.please_test_connect')]);
        }

        try {
            // 更新 .env 文件
            $this->updateEnvFile($dbConfig);

            // 运行数据库迁移
            Artisan::call('migrate', ['--force' => true]);

            // 初始化数据
            if ($initdata == '1') {
                $this->initData($dbConfig['prefix']);
            }

            // 创建管理员账号
            $adminRandom = md5(uniqid());
            $admin = Admin::create([
                'admin_name' => $account,
                'admin_pwd' => md5($password . $adminRandom),
                'admin_random' => $adminRandom,
                'admin_status' => 1,
                'admin_auth' => '',
            ]);

            if (!$admin) {
                return response()->json(['code' => 0, 'msg' => __('install.admin_name_err')]);
            }

            // 更新配置文件
            $this->updateConfig($installDir, session('install_lang', 'zh-cn'));

            // 创建安装锁定文件
            File::put(storage_path('app/install.lock'), date('Y-m-d H:i:s'));

            $rootUrl = url('/');
            return response()->json([
                'code' => 1,
                'msg' => __('install.is_ok'),
                'url' => $rootUrl . '/admin',
            ]);
        } catch (\Exception $e) {
            return response()->json(['code' => 0, 'msg' => __('install.install_error') . ': ' . $e->getMessage()]);
        }
    }

    /**
     * 检查是否已安装
     */
    private function isInstalled()
    {
        return File::exists(storage_path('app/install.lock'));
    }

    /**
     * 环境检测
     */
    private function checkEnv()
    {
        $items = [
            'os' => [
                __('install.os'),
                __('install.not_limited'),
                'Windows/Unix',
                PHP_OS,
                'ok',
            ],
            'php' => [
                __('install.php'),
                '8.0',
                '8.0及以上',
                PHP_VERSION,
                version_compare(PHP_VERSION, '8.0.0', '>=') ? 'ok' : 'no',
            ],
        ];

        return $items;
    }

    /**
     * 目录权限检查
     */
    private function checkDir()
    {
        $items = [
            ['dir', storage_path('app/backup'), __('install.read_and_write'), __('install.read_and_write'), is_writable(storage_path('app/backup')) ? 'ok' : 'no'],
            ['dir', storage_path('app/update'), __('install.read_and_write'), __('install.read_and_write'), is_writable(storage_path('app/update')) ? 'ok' : 'no'],
            ['dir', storage_path('logs'), __('install.read_and_write'), __('install.read_and_write'), is_writable(storage_path('logs')) ? 'ok' : 'no'],
            ['dir', storage_path('framework/cache'), __('install.read_and_write'), __('install.read_and_write'), is_writable(storage_path('framework/cache')) ? 'ok' : 'no'],
            ['dir', storage_path('framework/sessions'), __('install.read_and_write'), __('install.read_and_write'), is_writable(storage_path('framework/sessions')) ? 'ok' : 'no'],
            ['dir', storage_path('framework/views'), __('install.read_and_write'), __('install.read_and_write'), is_writable(storage_path('framework/views')) ? 'ok' : 'no'],
            ['dir', public_path('upload'), __('install.read_and_write'), __('install.read_and_write'), is_writable(public_path('upload')) ? 'ok' : 'no'],
        ];

        return $items;
    }

    /**
     * 函数及扩展检查
     */
    private function checkFunc()
    {
        $items = [
            ['pdo', __('install.support'), class_exists('PDO') ? 'yes' : 'no', __('install.class')],
            ['pdo_mysql', __('install.support'), extension_loaded('pdo_mysql') ? 'yes' : 'no', __('install.model')],
            ['zip', __('install.support'), extension_loaded('zip') ? 'yes' : 'no', __('install.model')],
            ['fileinfo', __('install.support'), extension_loaded('fileinfo') ? 'yes' : 'no', __('install.model')],
            ['curl', __('install.support'), extension_loaded('curl') ? 'yes' : 'no', __('install.model')],
            ['xml', __('install.support'), extension_loaded('xml') ? 'yes' : 'no', __('install.function')],
            ['file_get_contents', __('install.support'), function_exists('file_get_contents') ? 'yes' : 'no', __('install.function')],
            ['mb_strlen', __('install.support'), function_exists('mb_strlen') ? 'yes' : 'no', __('install.function')],
        ];

        return $items;
    }

    /**
     * 更新 .env 文件
     */
    private function updateEnvFile($config)
    {
        $envPath = base_path('.env');
        $env = File::get($envPath);

        $env = preg_replace('/DB_CONNECTION=(.*)/', 'DB_CONNECTION=mysql', $env);
        $env = preg_replace('/DB_HOST=(.*)/', 'DB_HOST=' . $config['host'], $env);
        $env = preg_replace('/DB_PORT=(.*)/', 'DB_PORT=' . $config['port'], $env);
        $env = preg_replace('/DB_DATABASE=(.*)/', 'DB_DATABASE=' . $config['database'], $env);
        $env = preg_replace('/DB_USERNAME=(.*)/', 'DB_USERNAME=' . $config['username'], $env);
        $env = preg_replace('/DB_PASSWORD=(.*)/', 'DB_PASSWORD=' . $config['password'], $env);
        $env = preg_replace('/DB_PREFIX=(.*)/', 'DB_PREFIX=' . $config['prefix'], $env);

        if (!str_contains($env, 'DB_PREFIX')) {
            $env .= "\nDB_PREFIX=" . $config['prefix'] . "\n";
        }

        File::put($envPath, $env);
    }

    /**
     * 初始化数据
     */
    private function initData($prefix)
    {
        $initDataFile = base_path('database/sql/initdata.sql');
        if (File::exists($initDataFile)) {
            $sql = File::get($initDataFile);
            $sql = str_replace('mac_', $prefix, $sql);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    DB::statement($statement);
                }
            }
        }
    }

    /**
     * 更新配置文件
     */
    private function updateConfig($installDir, $lang)
    {
        $config = config('maccms', []);
        $config['app']['cache_flag'] = substr(md5(time()), 0, 10);
        $config['app']['lang'] = $lang;
        $config['api']['vod']['status'] = 0;
        $config['api']['art']['status'] = 0;
        $config['interface']['status'] = 0;
        $config['interface']['pass'] = bin2hex(random_bytes(8));
        $config['site']['install_dir'] = $installDir;

        // 保存配置（这里需要实现配置保存逻辑）
        // 可以使用 config() 或直接写入配置文件
    }
}

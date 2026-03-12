<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;
use Throwable;

class SystemController extends BaseController
{
    public function configlang(Request $request)
    {
        try {
            $lang = strtolower(trim((string) $request->input('lang', '')));
            $map = [
                'zh' => 'zh-cn',
                'en' => 'en-us',
                'zh-cn' => 'zh-cn',
                'en-us' => 'en-us',
                'zh-tw' => 'zh-tw',
                'ja' => 'ja-jp',
                'ko' => 'ko-kr',
                'fr' => 'fr-fr',
                'de' => 'de-de',
                'es' => 'es-es',
                'pt' => 'pt-pt',
            ];
            $normalized = $map[$lang] ?? $lang;
            if ($normalized === '') {
                return response()->json(['code' => 1001, 'msg' => __('param_err')]);
            }
            $this->saveConfig(['lang' => $normalized], 'app');
            // 立即生效
            $localeMap = [
                'zh-cn' => 'zh',
                'zh-tw' => 'zh_TW',
                'en-us' => 'en',
                'de-de' => 'de',
                'es-es' => 'es',
                'fr-fr' => 'fr',
                'ja-jp' => 'ja',
                'ko-kr' => 'ko',
                'pt-pt' => 'pt',
            ];
            $laravelLocale = $localeMap[$normalized] ?? null;
            if ($laravelLocale) {
                app()->setLocale($laravelLocale);
                config(['app.locale' => $laravelLocale, 'maccms.app.lang' => $normalized]);
            }
            $this->clearCache();
            try {
                if (app()->bound('session')) {
                    session(['maccms_app_lang' => $normalized]);
                }
            } catch (Throwable $e) {
            }

            return response()
                ->json(['code' => 1, 'msg' => '语言已切换'])
                ->cookie('maccms_locale', $normalized, 60 * 24 * 365);
        } catch (Throwable $e) {
            return response()->json(['code' => 1001, 'msg' => '语言切换失败']);
        }
    }
    public function configurl(Request $request)
    {
        if ($request->isMethod('post')) {
            $view = (array) $request->input('view', []);
            $path = (array) $request->input('path', []);
            $rewrite = (array) $request->input('rewrite', []);
            if (!empty($view)) {
                $this->saveConfig($view, 'view');
            }
            if (!empty($path)) {
                $this->saveConfig($path, 'path');
            }
            if (!empty($rewrite)) {
                $this->saveConfig($rewrite, 'rewrite');
            }
            $this->clearCache();
            return $this->success('URL配置保存成功', [], route('admin.system.configurl', ['_t' => time()]));
        }
        $configFile = config_path('maccms.php');
        $config = [];
        if (File::exists($configFile)) {
            $loaded = include $configFile;
            if (is_array($loaded)) {
                $config = $loaded;
            }
        }
        if (!is_array($config) || empty($config)) {
            $config = config('maccms', []);
        }
        return view('admin.system.configurl', compact('config'));
    }

    public function config(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = $request->except(['_token']);
            
            // 处理配置数据
            $this->processConfig($config);
            
            // 保存配置到文件
            $this->saveConfig($config);
            
            // 清理缓存
            $this->clearCache();
            
            return $this->success('配置保存成功', [], route('admin.system.config', ['_t' => time()]));
        }

        $config = config('maccms');
        $templates = [];
        $templateRoot = base_path('template');
        if (File::isDirectory($templateRoot)) {
            $templates = array_values(array_map('basename', File::directories($templateRoot)));
            sort($templates);
        }
        if (empty($templates)) {
            $templates = ['default'];
        }

        $langs = [
            'zh-cn',
            'en-us',
            'zh-tw',
            'ja-jp',
            'ko-kr',
            'fr-fr',
            'de-de',
            'es-es',
            'pt-pt',
        ];

        return view('admin.system.config', compact('config', 'templates', 'langs'));
    }

    public function configseo(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = $request->except(['_token']);
            $this->saveConfig($config, 'seo');
            $this->clearCache();
            return $this->success('SEO配置保存成功', [], route('admin.system.configseo', ['_t' => time()]));
        }

        $config = config('maccms.seo', []);
        return view('admin.system.configseo', compact('config'));
    }

    public function configuser(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = (array) $request->input('user', []);
            $this->saveConfig($config, 'user');
            $this->clearCache();
            return $this->success('用户配置保存成功', [], route('admin.system.configuser', ['_t' => time()]));
        }

        $config = ['user' => config('maccms.user', [])];
        return view('admin.system.configuser', compact('config'));
    }

    public function configcomment(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->saveConfig((array) $request->input('gbook', []), 'gbook');
            $this->saveConfig((array) $request->input('comment', []), 'comment');
            $this->clearCache();
            return $this->success('评论配置保存成功', [], route('admin.system.configcomment', ['_t' => time()]));
        }

        $config = config('maccms', []);
        return view('admin.system.configcomment', compact('config'));
    }

    public function configupload(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = (array) $request->input('upload', []);
            $this->saveConfig($config, 'upload');
            $this->clearCache();
            return $this->success('上传配置保存成功', [], route('admin.system.configupload', ['_t' => time()]));
        }

        $config = ['upload' => config('maccms.upload', [])];

        $extends = ['ext_list' => []];
        $extendDir = base_path('app/Libraries/upload');
        if (File::isDirectory($extendDir)) {
            foreach (File::files($extendDir) as $file) {
                $base = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $class = 'App\\Libraries\\Upload\\' . $base;
                if (!class_exists($class)) {
                    continue;
                }
                try {
                    $instance = new $class();
                    $extends['ext_list'][$base] = (string) ($instance->name ?? $base);
                } catch (Throwable $e) {
                    $extends['ext_list'][$base] = $base;
                }
            }
            ksort($extends['ext_list']);
        }

        return view('admin.system.configupload', compact('config', 'extends'));
    }

    public function configinterface(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = (array) $request->input('interface', []);
            if ((int) ($config['status'] ?? 0) === 1 && mb_strlen((string) ($config['pass'] ?? '')) < 16) {
                return $this->error(__('admin/system/configinterface/pass_check'));
            }
            $this->saveConfig($config, 'interface');
            $this->clearCache();
            return $this->success('接口配置保存成功');
        }

        $config = config('maccms', []);
        return view('admin.system.configinterface', compact('config'));
    }

    public function configpay(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = $request->except(['_token']);
            $this->saveConfig($config, 'pay');
            $this->clearCache();
            return $this->success('支付配置保存成功');
        }

        $config = config('maccms.pay', []);
        return view('admin.system.configpay', compact('config'));
    }

    public function configcollect(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = (array) $request->input('collect', []);
            $this->saveConfig($config, 'collect');
            $this->clearCache();
            return $this->success('采集配置保存成功');
        }

        $config = ['collect' => config('maccms.collect', [])];
        return view('admin.system.configcollect', compact('config'));
    }

    public function configapi(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = $request->except(['_token']);
            $this->saveConfig($config, 'api');
            $this->clearCache();
            return $this->success('API配置保存成功');
        }

        $config = config('maccms.api', []);
        return view('admin.system.configapi', compact('config'));
    }

    public function configconnect(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = (array) $request->input('connect', []);
            $this->saveConfig($config, 'connect');
            $this->clearCache();
            return $this->success('第三方登录配置保存成功');
        }

        $config = config('maccms', []);
        return view('admin.system.configconnect', compact('config'));
    }

    public function configweixin(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = $request->except(['_token']);
            $this->saveConfig($config, 'weixin');
            $this->clearCache();
            return $this->success('微信配置保存成功');
        }

        $config = config('maccms.weixin', []);
        return view('admin.system.configweixin', compact('config'));
    }

    public function configview(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = $request->except(['_token']);
            $this->saveConfig($config, 'view');
            $this->clearCache();
            return $this->success('视图配置保存成功');
        }

        $config = config('maccms.view', []);
        return view('admin.system.configview', compact('config'));
    }

    public function configpath(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = $request->except(['_token']);
            $this->saveConfig($config, 'path');
            $this->clearCache();
            return $this->success('路径配置保存成功');
        }

        $config = config('maccms.path', []);
        return view('admin.system.configpath', compact('config'));
    }

    public function configrewrite(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = $request->except(['_token']);
            $this->saveConfig($config, 'rewrite');
            $this->clearCache();
            return $this->success('重写配置保存成功');
        }

        $config = config('maccms.rewrite', []);
        return view('admin.system.configrewrite', compact('config'));
    }

    public function configemail(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = (array) $request->input('email', []);
            $this->saveConfig($config, 'email');
            $this->clearCache();
            return $this->success('邮件配置保存成功');
        }

        $config = config('maccms', []);
        return view('admin.system.configemail', compact('config'));
    }

    public function configplay(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = (array) $request->input('play', []);
            $this->saveConfig($config, 'play');
            $this->clearCache();
            return $this->success('播放配置保存成功');
        }

        $config = config('maccms', []);
        $play = (array) config('maccms.play', []);
        return view('admin.system.configplay', compact('config', 'play'));
    }

    public function configsms(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = $request->except(['_token']);
            $this->saveConfig($config, 'sms');
            $this->clearCache();
            return $this->success('短信配置保存成功');
        }

        $config = config('maccms.sms', []);
        return view('admin.system.configsms', compact('config'));
    }

    public function testEmail(Request $request)
    {
        $type = strtolower(trim((string) $request->input('type', '')));
        $to = trim((string) $request->input('test', ''));
        $nick = trim((string) $request->input('nick', ''));
        $emailConfig = config('maccms.email', []);

        if ($type === '' || $to === '') {
            return response()->json(['code' => 1001, 'msg' => __('param_err')]);
        }

        $driverConfig = (array) ($emailConfig[$type] ?? []);
        if (empty($driverConfig)) {
            return response()->json(['code' => 1001, 'msg' => __('email_not_config')]);
        }

        $title = (string) data_get($emailConfig, 'tpl.test_title', '测试邮件');
        $body = (string) data_get($emailConfig, 'tpl.test_body', '测试邮件内容');
        $minutes = max(1, (int) ($emailConfig['time'] ?? 5));
        $vars = [
            'maccms' => [
                'site_name' => (string) config('maccms.site.site_name', 'maccms'),
            ],
            'code' => (string) random_int(100000, 999999),
            'time' => $minutes,
        ];

        $title = $this->renderSimpleTemplate($title, $vars);
        $body = html_entity_decode($this->renderSimpleTemplate($body, $vars), ENT_QUOTES, 'UTF-8');

        try {
            $result = $this->sendTestMail($type, $to, $title, $body, array_merge($driverConfig, [
                'nick' => $nick !== '' ? $nick : (string) ($emailConfig['nick'] ?? ''),
            ]));
        } catch (Throwable $e) {
            return response()->json(['code' => 1001, 'msg' => __('test_err') . '：' . $e->getMessage()]);
        }

        if (($result['code'] ?? 0) === 1) {
            return response()->json(['code' => 1, 'msg' => __('test_ok')]);
        }

        return response()->json([
            'code' => 1001,
            'msg' => __('test_err') . '：' . ($result['msg'] ?? 'unknown error'),
        ]);
    }

    public function testCache(Request $request)
    {
        $type = strtolower(trim((string) $request->input('type', '')));
        $host = trim((string) $request->input('host', ''));
        $port = (int) $request->input('port', 0);
        $username = trim((string) $request->input('username', ''));
        $password = trim((string) $request->input('password', ''));
        $db = (int) $request->input('db', 0);

        if ($type === '') {
            return response()->json(['code' => 1001, 'msg' => __('param_err')]);
        }

        try {
            $this->assertCacheConnection($type, $host, $port, $username, $password, $db);
            return response()->json(['code' => 1, 'msg' => __('test_ok')]);
        } catch (Throwable $e) {
            return response()->json(['code' => 1001, 'msg' => __('test_err') . '：' . $e->getMessage()]);
        }
    }

    protected function processConfig(&$config)
    {
        // 处理特殊配置项
        if (isset($config['app']['search_vod_rule']) && is_array($config['app']['search_vod_rule'])) {
            $config['app']['search_vod_rule'] = implode('|', $config['app']['search_vod_rule']);
        }
        if (isset($config['app']['search_art_rule']) && is_array($config['app']['search_art_rule'])) {
            $config['app']['search_art_rule'] = implode('|', $config['app']['search_art_rule']);
        }
        if (empty($config['app']['cache_flag'])) {
            $config['app']['cache_flag'] = substr(md5(time()), 0, 10);
        }
    }

    protected function saveConfig($config, $section = null)
    {
        $configFile = config_path('maccms.php');
        $currentConfig = [];
        if (File::exists($configFile)) {
            $loaded = include $configFile;
            if (is_array($loaded)) {
                $currentConfig = $loaded;
            }
        }

        if ($section) {
            $currentSection = $currentConfig[$section] ?? [];
            if (!is_array($currentSection)) {
                $currentSection = [];
            }
            $currentConfig[$section] = array_replace_recursive($currentSection, (array) $config);
        } else {
            $currentConfig = array_replace_recursive($currentConfig, (array) $config);
        }

        $content = "<?php\n\nreturn " . var_export($currentConfig, true) . ";\n";
        File::put($configFile, $content);
        clearstatcache(true, $configFile);
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($configFile, true);
        }
    }

    protected function sendTestMail(string $type, string $to, string $title, string $body, array $config): array
    {
        if (($config['secure'] ?? '') === 'tsl') {
            $config['secure'] = 'tls';
        }

        return match ($type) {
            'phpmailer' => $this->sendSmtpMail($to, $title, $body, $config),
            default => ['code' => 1001, 'msg' => 'unsupported mail driver: ' . $type],
        };
    }

    protected function renderSimpleTemplate(string $template, array $vars): string
    {
        return preg_replace_callback('/\{\$([A-Za-z0-9_.]+)\}/', static function (array $matches) use ($vars) {
            return (string) data_get($vars, $matches[1], '');
        }, $template) ?? $template;
    }

    protected function assertCacheConnection(string $type, string $host, int $port, string $username, string $password, int $db): void
    {
        if ($type === 'file') {
            $directory = storage_path('framework/cache');
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            if (!is_writable($directory)) {
                throw new \RuntimeException('cache directory is not writable');
            }

            return;
        }

        if ($host === '' || $port <= 0) {
            throw new \InvalidArgumentException(__('param_err'));
        }

        if ($type === 'redis') {
            if (!class_exists(\Redis::class)) {
                throw new \RuntimeException('php redis extension not installed');
            }

            $redis = new \Redis();
            if (@$redis->connect($host, $port, 3) === false) {
                throw new \RuntimeException('redis connect failed');
            }
            if ($password !== '') {
                $redis->auth($password);
            }
            if ($db > 0) {
                $redis->select($db);
            }
            $redis->set('maccms_cache_test', 'test', 10);
            $redis->del('maccms_cache_test');
            $redis->close();
            return;
        }

        if ($type === 'memcache') {
            if (!class_exists(\Memcache::class)) {
                throw new \RuntimeException('php memcache extension not installed');
            }

            $memcache = new \Memcache();
            if (@$memcache->connect($host, $port) === false) {
                throw new \RuntimeException('memcache connect failed');
            }
            $memcache->set('maccms_cache_test', 'test', 0, 10);
            $memcache->delete('maccms_cache_test');
            $memcache->close();
            return;
        }

        if ($type === 'memcached') {
            if (!class_exists(\Memcached::class)) {
                throw new \RuntimeException('php memcached extension not installed');
            }

            $memcached = new \Memcached();
            $memcached->addServer($host, $port);
            if ($username !== '' || $password !== '') {
                $memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
                $memcached->setSaslAuthData($username, $password);
            }
            $memcached->set('maccms_cache_test', 'test', 10);
            if ($memcached->getResultCode() !== \Memcached::RES_SUCCESS) {
                throw new \RuntimeException('memcached connect failed');
            }
            $memcached->delete('maccms_cache_test');
            return;
        }

        throw new \RuntimeException('unsupported cache driver: ' . $type);
    }

    protected function sendSmtpMail(string $to, string $title, string $body, array $config): array
    {
        $host = trim((string) ($config['host'] ?? ''));
        $port = (int) ($config['port'] ?? 0);
        $username = trim((string) ($config['username'] ?? ''));
        $password = (string) ($config['password'] ?? '');
        $secure = trim((string) ($config['secure'] ?? ''));
        $nick = trim((string) ($config['nick'] ?? ''));

        if ($host === '' || $port <= 0 || $username === '') {
            return ['code' => 1001, 'msg' => 'smtp config incomplete'];
        }

        $transport = new EsmtpTransport($host, $port, $secure !== '' ? $secure : null);
        $transport->setUsername($username);
        $transport->setPassword($password);

        $mailer = new Mailer($transport);
        $email = (new Email())
            ->from($nick !== '' ? sprintf('%s <%s>', $nick, $username) : $username)
            ->to($to)
            ->subject($title)
            ->html($body);

        $mailer->send($email);

        return ['code' => 1, 'msg' => 'ok'];
    }
}

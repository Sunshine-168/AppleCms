<?php

namespace App\Services\Video;

use App\Models\Video\VideoModel;
use App\Models\Video\VideoOption;
use App\Models\Video\VideoPlayerModel;
use App\Models\Video\VideoStatModel;
use App\Models\Video\VideoTypeModel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VideoInstallService
{
    public function locked(): bool
    {
        return is_file($this->lockPath());
    }

    public function lockPath(): string
    {
        return storage_path('app/install.lock');
    }

    public function alreadyInstalled(): bool
    {
        if ($this->locked()) {
            return true;
        }
        try {
            return Schema::hasTable('sys_user') && DB::table('sys_user')->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return list<array{key:string,label:string,ok:bool,detail:string,required:bool}> */
    public function checks(): array
    {
        $this->ensureEnvFile();
        $env = is_file(base_path('.env'));
        $key = $env && (string) config('app.key') !== '';

        return [
            [
                'key' => 'php',
                'label' => 'PHP 8.3 或更高',
                'ok' => version_compare(PHP_VERSION, '8.3.0', '>='),
                'detail' => PHP_VERSION,
                'required' => true,
            ],
            [
                'key' => 'pdo',
                'label' => 'PDO 扩展',
                'ok' => extension_loaded('pdo'),
                'detail' => extension_loaded('pdo') ? '已安装' : '未安装',
                'required' => true,
            ],
            [
                'key' => 'mbstring',
                'label' => 'mbstring 扩展',
                'ok' => extension_loaded('mbstring'),
                'detail' => extension_loaded('mbstring') ? '已安装' : '未安装',
                'required' => true,
            ],
            [
                'key' => 'openssl',
                'label' => 'openssl 扩展',
                'ok' => extension_loaded('openssl'),
                'detail' => extension_loaded('openssl') ? '已安装' : '未安装',
                'required' => true,
            ],
            [
                'key' => 'storage',
                'label' => 'storage 目录可写',
                'ok' => is_writable(storage_path()),
                'detail' => storage_path(),
                'required' => true,
            ],
            [
                'key' => 'cache',
                'label' => 'bootstrap/cache 可写',
                'ok' => is_dir(base_path('bootstrap/cache')) && is_writable(base_path('bootstrap/cache')),
                'detail' => 'bootstrap/cache',
                'required' => true,
            ],
            [
                'key' => 'env',
                'label' => '.env 配置文件',
                'ok' => $env,
                'detail' => $env ? '已就绪' : '将从 .env.example 复制',
                'required' => true,
            ],
            [
                'key' => 'key',
                'label' => '应用密钥',
                'ok' => $key,
                'detail' => $key ? '已有' : '安装时自动生成',
                'required' => false,
            ],
        ];
    }

    public function requiredPassed(array $checks): bool
    {
        foreach ($checks as $check) {
            if ($check['required'] && ! $check['ok']) {
                return false;
            }
        }

        return true;
    }

    /** @return array{ok:bool,error:?string} */
    public function databaseStatus(?array $input = null): array
    {
        try {
            if (is_array($input) && ($input['db_connection'] ?? '') !== '') {
                $this->applyDatabase($input);
            }
            DB::connection()->getPdo();

            return ['ok' => true, 'error' => null];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => mb_substr($e->getMessage(), 0, 180)];
        }
    }

    /** @param  array<string, mixed>  $input */
    public function run(array $input): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }
        $this->prepare($input);
        $this->migrate();
        $this->seedAccount($input);
        if (! empty($input['seed_demo'])) {
            $this->seedDemo();
        }
        $this->finish($input);
    }

    /** @param  array<string, mixed>  $input */
    public function prepare(array $input): void
    {
        $this->ensureEnvFile();
        $this->ensureAppKey();
        $this->writeEnv([
            'APP_NAME' => (string) ($input['site_name'] ?? 'LaraVideo'),
            'APP_URL' => rtrim((string) ($input['app_url'] ?? config('app.url')), '/'),
            'SESSION_DRIVER' => 'file',
            'CACHE_STORE' => 'file',
            'QUEUE_CONNECTION' => 'sync',
        ]);
        if (($input['db_connection'] ?? '') !== '') {
            $this->applyDatabase($input);
        }
        $this->ensureSqliteFile();
    }

    public function migrate(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }

    /** @param  array<string, mixed>  $input */
    public function seedAccount(array $input): void
    {
        $username = trim((string) ($input['admin_name'] ?? 'admin'));
        if ($username === '') {
            $username = 'admin';
        }
        $password = (string) ($input['admin_password'] ?? '');
        if (strlen($password) < 6) {
            throw new \RuntimeException('管理员密码至少 6 位');
        }
        $now = time();
        $email = (string) ($input['admin_email'] ?? '');
        if (Schema::hasTable('sys_user') && ! DB::table('sys_user')->exists()) {
            DB::table('sys_user')->insert([
                'id' => 1,
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'remark' => '超级管理员',
                'role' => 1,
                'role_id' => 0,
                'status' => 1,
                'token' => '',
                'create_time' => $now,
                'update_time' => $now,
            ]);
        }
        if (Schema::hasTable('video_options')) {
            VideoOption::query()->updateOrCreate(['k' => 'site_title'], ['v' => (string) ($input['site_name'] ?? 'LaraVideo'), 'updated_at' => $now]);
        }
        if (Schema::hasTable('video_types') && ! VideoTypeModel::query()->exists()) {
            Artisan::call('video:init');
        }
        if (Schema::hasTable('video_players') && VideoPlayerModel::query()->count() === 0) {
            Artisan::call('video:init');
        }
    }

    public function seedDemo(): void
    {
        if (! Schema::hasTable('videos') || VideoModel::query()->exists()) {
            return;
        }
        $typeId = (int) (VideoTypeModel::query()->value('id') ?: 0);
        if ($typeId < 1) {
            return;
        }
        $now = time();
        $id = VideoModel::query()->insertGetId([
            'type_id' => $typeId,
            'title' => '示例影片',
            'subtitle' => '安装后可删除',
            'cover' => '',
            'year' => date('Y'),
            'area' => '内地',
            'lang' => '国语',
            'remarks' => '示例',
            'description' => '这是安装向导写入的示例影片，采集或手动添加正片后可删除。',
            'status' => 1,
            'is_recommend' => 1,
            'letter' => 'S',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        VideoStatModel::query()->create([
            'video_id' => $id,
            'hits' => 0,
            'hits_day' => 0,
            'hits_week' => 0,
            'hits_month' => 0,
            'up' => 0,
            'down' => 0,
            'score' => 0,
            'score_all' => 0,
            'score_num' => 0,
            'updated_at' => $now,
        ]);
    }

    /** @param  array<string, mixed>  $input */
    public function finish(array $input): void
    {
        try {
            Artisan::call('storage:link', ['--force' => true]);
        } catch (\Throwable) {
        }
        $dir = dirname($this->lockPath());
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents(
            $this->lockPath(),
            'installed_at='.date('c')."\nadmin=".(string) ($input['admin_name'] ?? '')."\n"
        );
    }

    /** @param  array<string, mixed>  $input */
    public function applyDatabase(array $input): void
    {
        $type = (string) ($input['db_connection'] ?? '');
        if ($type === 'sqlite') {
            $path = database_path('database.sqlite');
            if (! is_file($path)) {
                $dir = dirname($path);
                if (! is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
                touch($path);
            }
            $this->writeEnv([
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => $path,
                'DB_HOST' => '',
                'DB_PORT' => '',
                'DB_USERNAME' => '',
                'DB_PASSWORD' => '',
            ]);
            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => $path,
            ]);
            DB::purge('sqlite');
            DB::setDefaultConnection('sqlite');
            DB::reconnect('sqlite');

            return;
        }
        if ($type !== 'mysql') {
            return;
        }
        $host = (string) ($input['db_host'] ?? '127.0.0.1');
        $port = (string) ($input['db_port'] ?? '3306');
        $database = (string) ($input['db_database'] ?? '');
        $username = (string) ($input['db_username'] ?? '');
        $password = (string) ($input['db_password'] ?? '');
        if ($database === '') {
            throw new \RuntimeException('请填写数据库名');
        }
        $this->writeEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ]);
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => $port,
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.username' => $username,
            'database.connections.mysql.password' => $password,
        ]);
        DB::purge('mysql');
        DB::setDefaultConnection('mysql');
        DB::reconnect('mysql');
    }

    public function ensureEnvFile(): void
    {
        $path = base_path('.env');
        if (! is_file($path) && is_file(base_path('.env.example'))) {
            copy(base_path('.env.example'), $path);
        }
    }

    public function ensureAppKey(): void
    {
        if ((string) config('app.key') !== '') {
            return;
        }
        Artisan::call('key:generate', ['--force' => true]);
    }

    /** @param  array<string, string>  $pairs */
    public function writeEnv(array $pairs): void
    {
        $path = base_path('.env');
        if (! is_file($path)) {
            $this->ensureEnvFile();
        }
        if (! is_file($path) || ! is_writable($path)) {
            throw new \RuntimeException('.env 无法写入');
        }
        $content = (string) file_get_contents($path);
        foreach ($pairs as $key => $value) {
            $line = $key.'='.$this->envEscape((string) $value);
            if (preg_match('/^'.preg_quote($key, '/').'=.*/m', $content)) {
                $content = preg_replace('/^'.preg_quote($key, '/').'=.*/m', $line, $content, 1) ?? $content;
            } else {
                $content = rtrim($content)."\n".$line."\n";
            }
        }
        file_put_contents($path, $content);
    }

    private function ensureSqliteFile(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }
        $path = (string) config('database.connections.sqlite.database');
        if ($path === '' || $path === ':memory:' || is_file($path)) {
            return;
        }
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        touch($path);
    }

    private function envEscape(string $value): string
    {
        if ($value === '' || ! preg_match('/[\s#"\'\\\\]/', $value)) {
            return $value;
        }

        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }
}

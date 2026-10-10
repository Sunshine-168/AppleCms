<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Video\VideoInstallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallController extends Controller
{
    public function __construct(private readonly VideoInstallService $install) {}

    public function index(Request $request): View|RedirectResponse
    {
        $way = (string) $request->query('way', 'web');
        if (! in_array($way, ['web', 'laravel', 'docker', 'deploy'], true)) {
            $way = 'web';
        }
        if ($this->install->alreadyInstalled() && $way === 'web') {
            return redirect('/admin/login');
        }

        $data = [
            'way' => $way,
            'checks' => [],
            'requiredOk' => true,
            'failN' => 0,
            'warnN' => 0,
            'passN' => 0,
            'dbOk' => false,
            'dbError' => '',
            'sqlitePath' => 'database/database.sqlite',
            'deployUrl' => url('/install?way=deploy'),
            'webInstallUrl' => url('/install'),
            'scheduleUrl' => url('/admin/help?topic=schedule'),
        ];
        if ($way !== 'web') {
            return view('install.index', $data);
        }

        $checks = $this->install->checks();
        $db = $this->install->databaseStatus();
        $failN = 0;
        $warnN = 0;
        $passN = 0;
        foreach ($checks as $check) {
            if ($check['ok']) {
                $passN++;
            } elseif ($check['required']) {
                $failN++;
            } else {
                $warnN++;
            }
        }
        usort($checks, static function (array $a, array $b): int {
            $rank = static function (array $row): int {
                if (! $row['ok'] && $row['required']) {
                    return 0;
                }
                if (! $row['ok']) {
                    return 1;
                }

                return 2;
            };

            return $rank($a) <=> $rank($b);
        });
        $data['checks'] = $checks;
        $data['requiredOk'] = $this->install->requiredPassed($checks);
        $data['failN'] = $failN;
        $data['warnN'] = $warnN;
        $data['passN'] = $passN;
        $data['dbOk'] = $db['ok'];
        $data['dbError'] = $db['error'];

        return view('install.index', $data);
    }

    public function probe(Request $request): JsonResponse
    {
        if ($this->install->locked()) {
            return response()->json(['ok' => false, 'message' => '已经安装过了'], 422);
        }
        $status = $this->install->databaseStatus($request->all());
        if (! $status['ok']) {
            return response()->json(['ok' => false, 'message' => $status['error'] ?: '连不上数据库'], 422);
        }

        return response()->json(['ok' => true, 'message' => '数据库可以连接']);
    }

    public function task(Request $request): JsonResponse
    {
        $phase = (string) $request->input('phase');
        if ($this->install->locked() && ! $request->session()->get('install.running')) {
            if ($phase === 'finish' && $this->lockIsFresh()) {
                $this->flashDone($request);

                return response()->json(['ok' => true]);
            }

            return response()->json(['ok' => false, 'message' => '已经安装过了'], 422);
        }
        $payload = $this->payload($request);
        try {
            if ($phase === 'prepare') {
                $request->session()->put('install.running', true);
                $this->install->prepare($payload);
            } elseif ($phase === 'migrate') {
                $this->install->applyDatabase($payload, false);
                $this->install->migrate();
            } elseif ($phase === 'account') {
                $this->validateAccount($request);
                $this->install->applyDatabase($payload, false);
                $this->install->seedAccount($payload);
            } elseif ($phase === 'demo') {
                $this->install->applyDatabase($payload, false);
                if ($request->boolean('seed_demo')) {
                    $this->install->seedDemo();
                }
            } elseif ($phase === 'finish') {
                $this->install->finish($payload);
                $request->session()->forget('install.running');
                $this->flashDone($request);
            } else {
                return response()->json(['ok' => false, 'message' => '未知步骤'], 422);
            }

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            $request->session()->forget('install.running');
            @unlink($this->install->lockPath());

            return response()->json(['ok' => false, 'message' => '安装失败：'.$e->getMessage()], 500);
        }
    }

    public function done(): View|RedirectResponse
    {
        $done = session('install_done');
        if (! is_array($done)) {
            return redirect($this->install->alreadyInstalled() ? '/admin/login' : '/install');
        }

        return view('install.done', [
            'username' => (string) ($done['username'] ?? 'admin'),
            'siteName' => (string) ($done['site_name'] ?? 'LaraVideo'),
            'demo' => (bool) ($done['demo'] ?? false),
        ]);
    }

    private function validateAccount(Request $request): void
    {
        $request->validate([
            'site_name' => 'required|string|max:80',
            'admin_name' => 'required|string|min:2|max:50',
            'admin_email' => 'nullable|email|max:120',
            'admin_password' => 'required|string|min:6|confirmed',
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(Request $request): array
    {
        return [
            'app_url' => rtrim((string) $request->input('app_url', $request->getSchemeAndHttpHost()), '/'),
            'site_name' => (string) $request->input('site_name', 'LaraVideo'),
            'admin_name' => (string) $request->input('admin_name', 'admin'),
            'admin_email' => (string) $request->input('admin_email', ''),
            'admin_password' => (string) $request->input('admin_password', ''),
            'seed_demo' => $request->boolean('seed_demo'),
            'db_connection' => (string) $request->input('db_connection', 'sqlite'),
            'db_host' => (string) $request->input('db_host', '127.0.0.1'),
            'db_port' => (string) $request->input('db_port', '3306'),
            'db_database' => (string) $request->input('db_database', ''),
            'db_username' => (string) $request->input('db_username', ''),
            'db_password' => (string) $request->input('db_password', ''),
        ];
    }

    private function flashDone(Request $request): void
    {
        $request->session()->put('install_done', [
            'username' => (string) $request->input('admin_name', 'admin'),
            'site_name' => (string) $request->input('site_name', 'LaraVideo'),
            'demo' => $request->boolean('seed_demo'),
        ]);
    }

    private function lockIsFresh(): bool
    {
        $path = $this->install->lockPath();
        $mtime = is_file($path) ? filemtime($path) : false;

        return $mtime !== false && (time() - $mtime) < 120;
    }
}

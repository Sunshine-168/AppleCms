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

    public function index(): View|RedirectResponse
    {
        if ($this->install->alreadyInstalled()) {
            return redirect('/admin/login');
        }
        $checks = $this->install->checks();
        $db = $this->install->databaseStatus();

        return view('install.index', [
            'checks' => $checks,
            'requiredOk' => $this->install->requiredPassed($checks),
            'dbOk' => $db['ok'],
            'dbError' => $db['error'],
        ]);
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
        if ($this->install->locked() && ! $request->session()->get('install.running')) {
            return response()->json(['ok' => false, 'message' => '已经安装过了'], 422);
        }
        $phase = (string) $request->input('phase');
        try {
            if ($phase === 'prepare') {
                $request->session()->put('install.running', true);
                $this->install->prepare($this->payload($request));
            } elseif ($phase === 'migrate') {
                $this->install->migrate();
            } elseif ($phase === 'account') {
                $this->validateAccount($request);
                $this->install->seedAccount($this->payload($request));
            } elseif ($phase === 'demo') {
                if ($request->boolean('seed_demo')) {
                    $this->install->seedDemo();
                }
            } elseif ($phase === 'finish') {
                $this->install->finish($this->payload($request));
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
        $request->session()->flash('install_done', [
            'username' => (string) $request->input('admin_name', 'admin'),
            'site_name' => (string) $request->input('site_name', 'LaraVideo'),
            'demo' => $request->boolean('seed_demo'),
        ]);
    }
}

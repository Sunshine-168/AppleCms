<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Plugins\PluginInstaller;
use App\Support\Plugins\PluginManager;
use App\Services\Video\VideoSettingService;
use App\Support\AdminOpLog;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class PluginController extends Controller
{
    public function index(PluginManager $plugins): View
    {
        return view('admin.plugins.index', [
            'plugins' => $plugins->listForAdmin(),
        ]);
    }

    public function show(string $id, PluginManager $plugins, VideoSettingService $settings): View
    {
        $plugin = $plugins->findForAdmin($id);
        if ($plugin === null) {
            abort(404);
        }

        return view('admin.plugins.show', [
            'plugin' => $plugin,
            'site' => $settings->site(),
        ]);
    }

    public function toggle(Request $request, string $id, PluginManager $plugins): RedirectResponse
    {
        $on = $request->boolean('enabled');
        $row = $plugins->findForAdmin($id);
        $name = (string) ($row['name'] ?? $id);
        try {
            $plugins->setEnabled($id, $on);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        AdminOpLog::write($on ? 'enable' : 'disable', ($on ? '启用了插件 ' : '停用了插件 ').$name, [
            'module' => '插件',
            'target_type' => 'plugins',
            'payload' => ['id' => $id],
        ]);

        return back()->with('status', $on ? admin_t('plugin.msg_on') : admin_t('plugin.msg_off'));
    }

    public function upload(Request $request, PluginInstaller $installer): JsonResponse
    {
        $file = $request->file('file');
        if (! $file instanceof UploadedFile) {
            return Ajax::fail(admin_t('plugin.err_file'));
        }
        try {
            $result = $installer->install($file, $request->boolean('replace'));
        } catch (\Throwable $e) {
            $msg = $e->getMessage();

            return Ajax::fail($msg !== '' ? $msg : admin_t('plugin.err_write'));
        }
        if (($result['status'] ?? '') === 'confirm') {
            return Ajax::fail((string) ($result['msg'] ?? admin_t('plugin.err_exists')), [
                'replaceable' => true,
                'id' => (string) ($result['id'] ?? ''),
            ]);
        }
        $pluginId = (string) ($result['id'] ?? '');
        $pluginName = (string) ($result['name'] ?? $pluginId);
        AdminOpLog::write('upload', '上传了插件'.($pluginName !== '' ? ' '.$pluginName : ''), [
            'module' => '插件',
            'target_type' => 'plugins',
            'payload' => ['id' => $pluginId],
        ]);

        return Ajax::success(
            ['id' => $pluginId],
            admin_t('plugin.upload_ok', ['name' => $pluginName !== '' ? $pluginName : $pluginId])
        );
    }

    public function uninstall(string $id, PluginInstaller $installer, PluginManager $plugins): JsonResponse
    {
        $row = $plugins->findForAdmin($id);
        $name = (string) ($row['name'] ?? $id);
        try {
            $installer->uninstall($id);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();

            return Ajax::fail($msg !== '' ? $msg : admin_t('plugin.err_write'));
        }
        AdminOpLog::write('uninstall', '卸载了插件 '.$name, [
            'module' => '插件',
            'target_type' => 'plugins',
            'payload' => ['id' => $id],
        ]);

        return Ajax::success(['id' => $id], admin_t('plugin.uninstall_ok'));
    }
}

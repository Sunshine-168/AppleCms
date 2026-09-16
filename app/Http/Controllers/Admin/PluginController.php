<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Plugins\PluginManager;
use App\Services\Video\VideoSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        try {
            $plugins->setEnabled($id, $on);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $on ? admin_t('plugin.msg_on') : admin_t('plugin.msg_off'));
    }
}

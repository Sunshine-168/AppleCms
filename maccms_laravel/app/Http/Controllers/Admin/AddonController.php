<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class AddonController extends Controller
{
    protected $addonPath;

    public function __construct()
    {
        $this->addonPath = base_path('addons');
    }

    public function index(Request $request)
    {
        $localAddons = $this->getLocalAddons();
        $onlineAddons = []; // Placeholder for online addons if needed

        return view('admin.addon.index', compact('localAddons'));
    }

    public function downloaded(Request $request)
    {
        // Simulate fetching online addons from maccms api
        // In a real migration, we might want to use the official API or a custom one
        // For now, let's just return the local ones as "downloaded"
        
        $localAddons = $this->getLocalAddons();
        return response()->json([
            'total' => count($localAddons),
            'rows' => array_values($localAddons)
        ]);
    }

    public function config(Request $request, $name = null)
    {
        if (!$name) {
            return back()->withErrors(['msg' => 'Addon name required']);
        }

        $addonDir = $this->addonPath . '/' . $name;
        if (!File::exists($addonDir)) {
            return back()->withErrors(['msg' => 'Addon directory not found']);
        }

        $configFile = $addonDir . '/config.php';
        $infoFile = $addonDir . '/info.ini';

        if (!File::exists($configFile) || !File::exists($infoFile)) {
            return back()->withErrors(['msg' => 'Config file not found']);
        }

        $info = parse_ini_file($infoFile);
        $config = require $configFile;

        if ($request->isMethod('post')) {
            $params = $request->input('row');
            
            // Iterate over existing config to update values
            foreach ($config as $k => &$v) {
                if (isset($params[$v['name']])) {
                    $v['value'] = $params[$v['name']];
                }
            }
            
            // Write config back to file
            $content = "<?php\nreturn " . var_export($config, true) . ";";
            File::put($configFile, $content);
            
            return redirect()->route('admin.addon.index')->with('success', 'Config saved successfully');
        }

        return view('admin.addon.config', compact('info', 'config', 'name'));
    }

    public function state(Request $request)
    {
        $name = $request->input('name');
        $action = $request->input('action');
        
        if (!$name) return response()->json(['code' => 0, 'msg' => 'Param error']);

        $infoFile = $this->addonPath . '/' . $name . '/info.ini';
        if (File::exists($infoFile)) {
            $info = parse_ini_file($infoFile);
            $info['state'] = ($action == 'enable') ? 1 : 0;
            
            // Write back to info.ini
            $this->writeIniFile($info, $infoFile);
            
            return response()->json(['code' => 1, 'msg' => 'Operation successful']);
        }

        return response()->json(['code' => 0, 'msg' => 'Addon info not found']);
    }

    protected function getLocalAddons()
    {
        $addons = [];
        if (!File::exists($this->addonPath)) {
            File::makeDirectory($this->addonPath);
        }

        $directories = File::directories($this->addonPath);
        
        foreach ($directories as $dir) {
            $infoFile = $dir . '/info.ini';
            if (File::exists($infoFile)) {
                $info = parse_ini_file($infoFile);
                $info['name'] = basename($dir);
                $info['url'] = route('admin.addon.config', ['name' => $info['name']]);
                
                // Add default values if missing
                $info['title'] = $info['title'] ?? $info['name'];
                $info['intro'] = $info['intro'] ?? '';
                $info['author'] = $info['author'] ?? '';
                $info['version'] = $info['version'] ?? '1.0.0';
                $info['state'] = $info['state'] ?? 0;
                
                $addons[$info['name']] = $info;
            }
        }
        
        return $addons;
    }

    protected function writeIniFile($data, $file)
    {
        $content = "";
        foreach ($data as $key => $value) {
            $content .= "{$key} = {$value}\n";
        }
        File::put($file, $content);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\DomainImportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DomainController extends BaseController
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $tmp = $request->input('domain', []);
            $domain = [];

            foreach ($tmp['site_url'] ?? [] as $k => $v) {
                if (empty($v)) continue;
                
                $domain[$v] = [
                    'site_url' => $v,
                    'site_name' => $tmp['site_name'][$k] ?? '',
                    'site_keywords' => $tmp['site_keywords'][$k] ?? '',
                    'site_description' => $tmp['site_description'][$k] ?? '',
                    'template_dir' => $tmp['template_dir'][$k] ?? 'default',
                    'html_dir' => $tmp['html_dir'][$k] ?? 'html',
                    'ads_dir' => $tmp['ads_dir'][$k] ?? 'ads',
                    'map_dir' => $tmp['map_dir'][$k] ?? 'map',
                ];
            }

            $this->saveConfig('domain', $domain);
            return $this->success('保存成功');
        }

        $templates = [];
        $templatePath = base_path('template');
        if (File::exists($templatePath)) {
            $dirs = File::directories($templatePath);
            foreach ($dirs as $dir) {
                $templates[] = basename($dir);
            }
        }

        $domainList = config('domain', []);

        return view('admin.domain.index', compact('templates', 'domainList'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $this->error('请选择要删除的数据');
        }

        $list = config('domain', []);
        unset($list[$ids]);
        
        $this->saveConfig('domain', $list);
        return $this->success('删除成功');
    }

    public function export()
    {
        $list = config('domain', []);
        $html = '';
        foreach ($list as $k => $v) {
            $html .= $v['site_url'] . '$' . $v['site_name'] . '$' . $v['site_keywords'] . '$' . 
                    $v['site_description'] . '$' . $v['template_dir'] . '$' . $v['html_dir'] . '$' . 
                    $v['ads_dir'] . '$' . $v['map_dir'] . "\n";
        }

        return response($html)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename=mac_domains.txt');
    }

    public function import(DomainImportRequest $request)
    {
        $file = $request->file('file');
        $data = file_get_contents($file->getRealPath());
        $lines = explode("\n", $data);

        $domain = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $parts = explode('$', $line);
            if (count($parts) >= 8) {
                $domain[$parts[0]] = [
                    'site_url' => $parts[0],
                    'site_name' => $parts[1] ?? '',
                    'site_keywords' => $parts[2] ?? '',
                    'site_description' => $parts[3] ?? '',
                    'template_dir' => $parts[4] ?? 'default',
                    'html_dir' => $parts[5] ?? 'html',
                    'ads_dir' => $parts[6] ?? 'ads',
                    'map_dir' => $parts[7] ?? 'map',
                ];
            }
        }

        $this->saveConfig('domain', $domain);
        return $this->success('导入成功');
    }

    protected function saveConfig($key, $data)
    {
        $configFile = config_path($key . '.php');
        $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";
        File::put($configFile, $content);
    }
}

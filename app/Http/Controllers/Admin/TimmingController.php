<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TimmingInfoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TimmingController extends BaseController
{
    public function index()
    {
        $list = config('timming', []);
        return view('admin.timming.index', compact('list'));
    }

    public function info(TimmingInfoRequest $request, $id = null)
    {
        $list = config('timming', []);

        if ($request->isMethod('post')) {
            $data = $request->except(['_token']);
            $name = $data['name'] ?? '';
            
            $data['weeks'] = is_array($data['weeks'] ?? []) ? implode(',', $data['weeks']) : '';
            $data['hours'] = is_array($data['hours'] ?? []) ? implode(',', $data['hours']) : '';
            
            $list[$name] = $data;
            $this->saveConfig('timming', $list);
            
            return $this->success('保存成功');
        }

        $info = $id ? ($list[$id] ?? []) : [];
        if (!empty($info['weeks'])) {
            $info['weeks'] = explode(',', $info['weeks']);
        }
        if (!empty($info['hours'])) {
            $info['hours'] = explode(',', $info['hours']);
        }

        return view('admin.timming.info', compact('info', 'id'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $this->error('请选择要删除的数据');
        }

        $list = config('timming', []);
        unset($list[$ids]);
        
        $this->saveConfig('timming', $list);
        return $this->success('删除成功');
    }

    public function field(Request $request)
    {
        $ids = $request->input('ids');
        $col = $request->input('col');
        $val = $request->input('val');

        if (empty($ids) || $col !== 'status') {
            return $this->error('参数错误');
        }

        $list = config('timming', []);
        $idArray = explode(',', $ids);
        
        foreach ($list as $k => &$v) {
            if (in_array($k, $idArray)) {
                $v[$col] = $val;
            }
        }
        
        $this->saveConfig('timming', $list);
        return $this->success('更新成功');
    }

    protected function saveConfig($key, $data)
    {
        $configFile = config_path($key . '.php');
        $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";
        File::put($configFile, $content);
    }
}

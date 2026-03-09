<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\VodserverInfoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class VodserverController extends BaseController
{
    protected $configKey = 'vodserver';

    public function index()
    {
        $list = config($this->configKey, []);
        return view('admin.vodserver.index', compact('list'));
    }

    public function info(VodserverInfoRequest $request, $id = null)
    {
        $list = config($this->configKey, []);

        if ($request->isMethod('post')) {
            $data = $request->except(['_token', 'flag']);
            
            $from = $data['from'] ?? '';
            if (is_numeric($from)) {
                $from .= '_';
            }
            
            // 安全检查
            if (strpos($from, '.') !== false || strpos($from, '/') !== false || strpos($from, '\\') !== false) {
                return $this->error('服务器标识包含非法字符');
            }
            
            $list[$from] = $data;
            
            // 按排序字段排序
            $sort = [];
            foreach ($list as $k => $v) {
                $sort[] = $v['sort'] ?? 0;
            }
            array_multisort($sort, SORT_DESC, SORT_FLAG_CASE, $list);
            
            // 保存配置
            $this->saveConfig($this->configKey, $list);
            Cache::forget('cache_data');
            
            return $this->success('保存成功');
        }

        $info = $id ? ($list[$id] ?? []) : [];

        return view('admin.vodserver.info', compact('info', 'id'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $this->error('请选择要删除的数据');
        }

        $list = config($this->configKey, []);
        unset($list[$ids]);
        
        $this->saveConfig($this->configKey, $list);
        Cache::forget('cache_data');
        
        return $this->success('删除成功');
    }

    public function field(Request $request)
    {
        $ids = $request->input('ids');
        $col = $request->input('col');
        $val = $request->input('val');

        if (empty($ids) || !in_array($col, ['parse_status', 'status'])) {
            return $this->error('参数错误');
        }

        $list = config($this->configKey, []);
        
        foreach ($list as $k => &$v) {
            $v[$col] = $val;
        }
        
        $this->saveConfig($this->configKey, $list);
        return $this->success('更新成功');
    }

    protected function saveConfig($key, $data)
    {
        $configFile = config_path($key . '.php');
        $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";
        File::put($configFile, $content);
    }
}

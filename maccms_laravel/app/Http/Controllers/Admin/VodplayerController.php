<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\VodplayerInfoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class VodplayerController extends BaseController
{
    protected $configKey = 'vodplayer';

    public function index()
    {
        $list = config($this->configKey, []);
        return view('admin.vodplayer.index', compact('list'));
    }

    public function info(VodplayerInfoRequest $request, $id = null)
    {
        $list = config($this->configKey, []);

        if ($request->isMethod('post')) {
            $data = $request->except(['_token', 'flag']);
            $code = $data['code'] ?? '';
            unset($data['code']);
            
            $from = $data['from'] ?? '';
            if (is_numeric($from)) {
                $from .= '_';
            }
            
            // 安全检查
            if (strpos($from, '.') !== false || strpos($from, '/') !== false || strpos($from, '\\') !== false) {
                return $this->error('播放器标识包含非法字符');
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
            
            // 保存播放器代码
            if (!empty($code)) {
                $playerPath = public_path('static/player');
                if (!File::exists($playerPath)) {
                    File::makeDirectory($playerPath, 0755, true);
                }
                File::put($playerPath . '/' . $from . '.js', $code);
            }
            
            Cache::forget('cache_data');
            return $this->success('保存成功');
        }

        $info = $id ? ($list[$id] ?? []) : [];
        if (!empty($info) && $id) {
            $playerFile = public_path('static/player/' . $id . '.js');
            if (File::exists($playerFile)) {
                $info['code'] = File::get($playerFile);
            }
        }

        return view('admin.vodplayer.info', compact('info', 'id'));
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

        if (empty($ids) || !in_array($col, ['ps', 'status'])) {
            return $this->error('参数错误');
        }

        $list = config($this->configKey, []);
        $idArray = explode(',', $ids);
        
        foreach ($list as $k => &$v) {
            if (in_array($k, $idArray)) {
                $v[$col] = $val;
            }
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

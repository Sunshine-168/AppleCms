<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WebsiteSaveRequest;
use App\Models\Website;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebsiteController extends Controller
{
    public function index(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? 20));

        $query = Website::query();
        
        if (!empty($param['type'])) {
            $query->where(function($q) use ($param) {
                $q->where('type_id', $param['type'])
                  ->orWhere('type_id_1', $param['type']);
            });
        }
        
        if (!empty($param['level'])) {
            $query->where('website_level', $param['level']);
        }
        
        if (isset($param['status']) && in_array($param['status'], ['0', '1'])) {
            $query->where('website_status', $param['status']);
        }
        
        if (!empty($param['lock'])) {
            $query->where('website_lock', $param['lock']);
        }
        
        if (!empty($param['pic'])) {
            if ($param['pic'] == '1') {
                $query->where('website_pic', '');
            } elseif ($param['pic'] == '2') {
                $query->where('website_pic', 'like', 'http%');
            } elseif ($param['pic'] == '3') {
                $query->where('website_pic', 'like', '%#err%');
            }
        }
        
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query->where('website_name', 'like', '%' . $wd . '%');
        }
        
        $order = 'website_time desc';
        if (!empty($param['repeat'])) {
            // Handle repeat check
            if ($param['page'] == 1) {
                $prefix = config('database.connections.mysql.prefix', 'mac_');
                DB::statement("DROP TABLE IF EXISTS {$prefix}tmpwebsite");
                DB::statement("CREATE TABLE `{$prefix}tmpwebsite` (`id1` int unsigned DEFAULT NULL, `name1` varchar(1024) NOT NULL DEFAULT '') ENGINE=MyISAM");
                DB::statement("INSERT INTO `{$prefix}tmpwebsite` (SELECT min(website_id)as id1,website_name as name1 FROM {$prefix}website GROUP BY name1 HAVING COUNT(name1)>1)");
            }
            $order = 'website_name asc';
        }
        
        $websites = $query->orderByRaw($order)
                         ->paginate($param['limit'], ['*'], 'page', $param['page']);
        
        // Check if needs to make static pages
        $config = config('maccms.view');
        foreach ($websites as $website) {
            $website->ismake = 1;
            if ($config['website_detail'] > 0 && $website->website_time_make < $website->website_time) {
                $website->ismake = 0;
            }
        }
        
        $types = Type::where('type_mid', 11)->get();
        $typeTree = $this->buildTypeTree($types);
        
        return view('admin.website.index', compact('websites', 'typeTree', 'param'));
    }

    public function info(WebsiteSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            if (!empty($id)) {
                $website = Website::findOrFail($id);
                $website->update($data);
            } else {
                $website = Website::create($data);
            }
            
            return redirect()->route('admin.website.index')->with('success', '保存成功');
        }
        
        $website = $id ? Website::findOrFail($id) : new Website();
        $types = Type::where('type_mid', 11)->get();
        
        return view('admin.website.info', compact('website', 'types'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->withErrors(['msg' => '请选择要删除的数据']);
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        Website::whereIn('website_id', $ids)->delete();
        
        return back()->with('success', '删除成功');
    }

    private function buildTypeTree($types)
    {
        $tree = [];
        $map = [];
        
        foreach ($types as $type) {
            $map[$type->type_id] = $type;
            $type->children = [];
        }
        
        foreach ($types as $type) {
            if ($type->type_pid == 0) {
                $tree[] = $type;
            } else {
                if (isset($map[$type->type_pid])) {
                    $map[$type->type_pid]->children[] = $type;
                }
            }
        }
        
        return $tree;
    }
}

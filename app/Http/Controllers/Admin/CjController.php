<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CjImportRequest;
use App\Http\Requests\Admin\CjSaveRequest;
use App\Models\Cj;
use App\Services\ImageSyncService;
use App\Utils\Pinyin;
use App\Utils\Collection as CollectionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CjController extends BaseController
{
    protected $isAll = false;

    public function __construct(protected ImageSyncService $imageSyncService)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? $this->pagesize));

        $query = DB::table('mac_cj_node');
        $total = $query->count();
        
        $list = $query->orderBy('nodeid', 'desc')
                     ->skip(($param['page'] - 1) * $param['limit'])
                     ->take($param['limit'])
                     ->get();

        return view('admin.cj.index', compact('list', 'total', 'param'));
    }

    public function info(CjSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $data = $request->input('data', []);
            $data['urlpage'] = (string)$request->input('urlpage' . ($data['sourcetype'] ?? 0), '');
            
            if (!empty($data['customize_config'])) {
                $customizeConfig = $data['customize_config'];
                unset($data['customize_config']);
                $configArray = [];
                foreach ($customizeConfig['name'] ?? [] as $k => $v) {
                    if (empty($v) || empty($customizeConfig['name'][$k])) continue;
                    $configArray[] = [
                        'name' => $customizeConfig['name'][$k],
                        'en_name' => $customizeConfig['en_name'][$k],
                        'rule' => $customizeConfig['rule'][$k],
                        'html_rule' => $customizeConfig['html_rule'][$k],
                    ];
                }
                $data['customize_config'] = json_encode($configArray, JSON_FORCE_OBJECT);
            }
            
            if ($id) {
                DB::table('mac_cj_node')->where('nodeid', $id)->update($data);
                return $this->success('更新成功');
            } else {
                DB::table('mac_cj_node')->insert($data);
                return $this->success('添加成功');
            }
        }

        $data = [];
        if ($id) {
            $data = DB::table('mac_cj_node')->where('nodeid', $id)->first();
            if (!empty($data->customize_config)) {
                $data->customize_config = json_decode($data->customize_config, true);
            }
        }

        return view('admin.cj.info', compact('data', 'id'));
    }

    public function program(Request $request, $id)
    {
        $node = DB::table('mac_cj_node')->where('nodeid', $id)->first();
        if (!$node) {
            return $this->error('节点不存在');
        }

        if ($request->isMethod('post')) {
            $programConfig = [];
            $modelFields = $request->input('model_field', []);
            $nodeFields = $request->input('node_field', []);
            $funcs = $request->input('funcs', []);
            
            foreach ($modelFields as $k => $v) {
                if (!empty($nodeFields[$k])) {
                    $programConfig['map'][$v] = $nodeFields[$k];
                    $programConfig['funcs'][$v] = $funcs[$k] ?? '';
                }
            }
            
            DB::table('mac_cj_node')
              ->where('nodeid', $id)
              ->update(['program_config' => json_encode($programConfig)]);
            
            return $this->success('保存成功');
        }

        $programConfig = [];
        if (!empty($node->program_config)) {
            $programConfig = json_decode($node->program_config, true);
        }

        $customizeConfig = [];
        if (!empty($node->customize_config)) {
            $customizeConfig = json_decode($node->customize_config, true);
        }

        $nodeField = ['title' => '标题', 'type' => '分类', 'content' => '内容'];
        if (is_array($customizeConfig)) {
            foreach ($customizeConfig as $v) {
                if (!empty($v['en_name']) && !empty($v['name'])) {
                    $nodeField[$v['en_name']] = $v['name'];
                }
            }
        }

        $table = $node->mid == 2 ? 'mac_art' : 'mac_vod';
        $columnList = DB::select("SHOW COLUMNS FROM {$table}");

        return view('admin.cj.program', compact('node', 'programConfig', 'nodeField', 'columnList'));
    }

    public function publish(Request $request, $id)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(20, intval($param['limit'] ?? $this->pagesize));

        $query = DB::table('mac_cj_content')->where('nodeid', $id);
        
        if (!empty($param['status'])) {
            $query->where('status', $param['status']);
        }

        $total = $query->count();
        $list = $query->orderBy('id', 'desc')
                     ->skip(($param['page'] - 1) * $param['limit'])
                     ->take($param['limit'])
                     ->get();

        $page = $param['page'];
        $limit = $param['limit'];

        return view('admin.cj.publish', compact('list', 'total', 'param', 'id', 'page', 'limit'));
    }

    public function show(Request $request, $id)
    {
        $info = DB::table('mac_cj_content')->where('id', $id)->first();
        if (!$info) {
            return $this->error('内容不存在');
        }

        if (!empty($info->data)) {
            $info->data = json_decode($info->data, true);
        }

        return view('admin.cj.show', compact('info'));
    }

    public function showUrl(Request $request)
    {
        $data = $request->input('data', []);
        if (!is_array($data)) {
            $data = [];
        }

        $sourceType = (string) ($data['sourcetype'] ?? '');
        $data['urlpage'] = (string) $request->input('urlpage' . $sourceType, '');
        $urls = CollectionUtil::url_list($data);

        return view('admin.cj.show_url', compact('urls'));
    }

    public function colUrl(Request $request, $id)
    {
        $node = $this->getNodeOrFail((int) $id);
        if (!$node) {
            return $this->error('节点不存在');
        }

        $config = $this->normalizeNodeConfig($node);
        $allPages = CollectionUtil::url_list($config);
        $totalPage = count($allPages);
        if ($totalPage < 1) {
            return $this->error('采集网址列表为空');
        }

        $param = $request->all();
        $param['id'] = (int) $id;
        $param['page'] = max(1, min((int) ($param['page'] ?? 1), $totalPage));

        $urlList = $allPages[$param['page'] - 1];
        $urlRows = CollectionUtil::get_url_lists($urlList, $config) ?: [];
        $duplicate = 0;

        foreach ($urlRows as $row) {
            $url = trim((string) ($row['url'] ?? ''));
            $title = trim(strip_tags((string) ($row['title'] ?? '')));
            if ($url === '' || $title === '') {
                $duplicate++;
                continue;
            }

            $md5 = md5($url);
            $exists = DB::table('mac_cj_history')->where('md5', $md5)->exists();
            if ($exists) {
                $duplicate++;
                continue;
            }

            DB::table('mac_cj_history')->insert(['md5' => $md5]);
            DB::table('mac_cj_content')->insert([
                'nodeid' => (int) $id,
                'status' => 1,
                'url' => $url,
                'title' => mb_substr($title, 0, 100),
                'data' => '',
            ]);
        }

        if ($param['page'] >= $totalPage) {
            DB::table('mac_cj_node')->where('nodeid', (int) $id)->update(['lastdate' => time()]);
        }

        $page = $param['page'];
        $total = count($urlRows);

        return view('admin.cj.col_url', compact('param', 'urlList', 'totalPage', 'duplicate', 'urlRows', 'page', 'total'))
            ->with([
                'url_list' => $urlList,
                'total_page' => $totalPage,
                're' => $duplicate,
                'url' => $urlRows,
            ]);
    }

    public function colContent(Request $request, $id)
    {
        $node = $this->getNodeOrFail((int) $id);
        if (!$node) {
            return $this->error('节点不存在');
        }

        $config = $this->normalizeNodeConfig($node);
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, (int) $request->input('limit', 20));

        $query = DB::table('mac_cj_content')
            ->where('nodeid', (int) $id)
            ->where('status', 1);
        $total = $query->count();
        $list = $query->orderBy('id')->forPage($page, $limit)->get();

        $processed = 0;
        foreach ($list as $item) {
            $content = CollectionUtil::get_content($item->url, $config);
            if (!is_array($content) || empty($content)) {
                continue;
            }

            DB::table('mac_cj_content')
                ->where('id', $item->id)
                ->update([
                    'status' => 2,
                    'data' => json_encode($content, JSON_UNESCAPED_UNICODE),
                ]);
            $processed++;
        }

        if ($processed > 0) {
            DB::table('mac_cj_node')->where('nodeid', (int) $id)->update(['lastdate' => time()]);
        }

        return $this->success("内容采集完成，本次处理 {$processed} 条", [], route('admin.cj.publish', ['id' => $id]));
    }

    public function contentInto(Request $request, $id)
    {
        $node = $this->getNodeOrFail((int) $id);
        if (!$node) {
            return $this->error('节点不存在');
        }

        $programConfig = json_decode((string) ($node->program_config ?? ''), true) ?: [];
        $fieldMap = (array) ($programConfig['map'] ?? []);
        if ($fieldMap === []) {
            return $this->error('请先配置字段映射');
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, (int) $request->input('limit', $this->pagesize));
        $all = (string) $request->input('all', '');
        $ids = $this->normalizeIds((string) $request->input('ids', ''));
        $options = [
            'opt' => (int) $request->input('opt', 0),
            'filter' => (int) $request->input('filter', 0),
            'filter_from' => (string) $request->input('filter_from', ''),
        ];

        $query = DB::table('mac_cj_content')
            ->where('nodeid', (int) $id)
            ->where('status', 2);
        if ($all !== '1' && $ids !== []) {
            $query->whereIn('id', $ids);
        }

        $list = $query->orderBy('id')->forPage($page, $limit)->get();
        if ($list->isEmpty()) {
            return $this->success('没有可导入的数据', [], route('admin.cj.publish', ['id' => $id]));
        }

        $imported = [];
        foreach ($list as $item) {
            $contentData = json_decode((string) $item->data, true) ?: [];
            $payload = $this->buildImportPayload($node, $fieldMap, (array) ($programConfig['funcs'] ?? []), $contentData);
            if ($payload === null) {
                continue;
            }

            if ($this->persistCollectedData((int) $node->mid, $payload, $options)) {
                $imported[] = $item->id;
            }
        }

        if ($imported !== []) {
            DB::table('mac_cj_content')->whereIn('id', $imported)->update(['status' => 3]);
        }

        return $this->success('内容导入完成，成功 ' . count($imported) . ' 条', [], route('admin.cj.publish', ['id' => $id]));
    }

    public function contentDel(Request $request)
    {
        $ids = $request->input('ids');
        $all = $request->input('all');
        
        if ($all) {
            DB::table('mac_cj_content')->truncate();
            DB::table('mac_cj_history')->truncate();
            return $this->success('清空成功');
        }

        if (empty($ids)) {
            return $this->error('请选择要删除的数据');
        }

        $idArray = is_array($ids) ? $ids : explode(',', $ids);
        
        // 删除历史记录
        $urls = DB::table('mac_cj_content')
                  ->whereIn('id', $idArray)
                  ->pluck('url')
                  ->map(function($url) {
                      return md5($url);
                  })
                  ->toArray();
        
        DB::table('mac_cj_history')->whereIn('md5', $urls)->delete();
        DB::table('mac_cj_content')->whereIn('id', $idArray)->delete();

        return $this->success('删除成功');
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $this->error('请选择要删除的数据');
        }

        $idArray = is_array($ids) ? $ids : explode(',', $ids);
        DB::table('mac_cj_node')->whereIn('nodeid', $idArray)->delete();

        return $this->success('删除成功');
    }

    public function export(Request $request, $id)
    {
        $node = DB::table('mac_cj_node')->where('nodeid', $id)->first();
        if (!$node) {
            return $this->error('节点不存在');
        }

        $content = base64_encode(json_encode($node));
        $filename = 'mac_cj_' . $node->name . '.txt';

        return response($content)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function import(CjImportRequest $request)
    {
        $file = $request->file('file');
        $content = base64_decode(file_get_contents($file->getRealPath()));
        $data = json_decode($content, true);

        if (!$data) {
            return $this->error('文件格式错误');
        }

        unset($data['nodeid']);
        DB::table('mac_cj_node')->insert($data);

        return $this->success('导入成功');
    }

    protected function getNodeOrFail(int $id): ?object
    {
        return DB::table('mac_cj_node')->where('nodeid', $id)->first();
    }

    protected function normalizeNodeConfig(object $node): array
    {
        $config = (array) $node;
        $config['customize_config'] = (string) ($config['customize_config'] ?? '');
        $config['program_config'] = (string) ($config['program_config'] ?? '');

        return $config;
    }

    protected function buildImportPayload(object $node, array $fieldMap, array $funcs, array $contentData): ?array
    {
        $prefix = (int) $node->mid === 2 ? 'art_' : 'vod_';
        $payload = [];
        $interfaceTypeMap = $this->getInterfaceTypeMap((int) $node->mid);

        foreach ($fieldMap as $modelField => $nodeField) {
            $value = $contentData[$nodeField] ?? null;
            $func = trim((string) ($funcs[$modelField] ?? ''));
            if ($func !== '' && function_exists($func)) {
                $value = $func($value);
            }

            if ($modelField === 'type_id') {
                $typeId = $this->resolveCollectedTypeId($value, (int) $node->mid, $interfaceTypeMap);
                if ($typeId > 0) {
                    $payload['type_id'] = $typeId;
                }
                continue;
            }

            $payload[$modelField] = is_string($value) ? trim($value) : $value;
        }

        $nameField = $prefix . 'name';
        if (empty($payload[$nameField])) {
            if (!empty($payload['title'])) {
                $payload[$nameField] = (string) $payload['title'];
            } else {
                return null;
            }
        }

        return $this->normalizeCollectedPayload((int) $node->mid, $payload);
    }

    protected function persistCollectedData(int $mid, array $payload, array $options = []): bool
    {
        $table = $mid === 2 ? 'mac_art' : 'mac_vod';
        $prefix = $mid === 2 ? 'art_' : 'vod_';
        $columns = Schema::getColumnListing($table);
        $data = array_intersect_key($payload, array_flip($columns));
        $nameField = $prefix . 'name';
        if (empty($data[$nameField])) {
            return false;
        }

        $rules = config('maccms.collect.' . ($mid === 2 ? 'art' : 'vod'), []);
        $picField = $prefix . 'pic';
        $syncPic = (int) ($rules['pic'] ?? 0) === 1;
        $existing = $this->findExistingCollectedRecord($mid, $table, $data, $rules);
        if (!$existing) {
            if (($options['opt'] ?? 0) === 2) {
                return false;
            }

            if ($mid !== 2) {
                $data = $this->applyVodCollectFilter($data, $options, 'add');
            }
            if ($syncPic && !empty($data[$picField] ?? null)) {
                $sync = $this->imageSyncService->syncImage((string) $data[$picField], $mid === 2 ? 'art' : 'vod');
                $data[$picField] = $sync['path'];
            }

            DB::table($table)->insert($data);
            return true;
        }

        $lockField = $prefix . 'lock';
        if ((int) ($existing->{$lockField} ?? 0) === 1) {
            return false;
        }
        if (($options['opt'] ?? 0) === 1) {
            return false;
        }

        if ($mid !== 2) {
            $data = $this->applyVodCollectFilter($data, $options, 'update');
        }
        $update = $this->buildCollectedUpdateData($mid, (array) $existing, $data, $rules);
        if (empty($update)) {
            return false;
        }

        if ($syncPic && !empty($update[$picField] ?? null)) {
            $sync = $this->imageSyncService->syncImage((string) $update[$picField], $mid === 2 ? 'art' : 'vod');
            $update[$picField] = $sync['path'];
        }

        $update[$prefix . 'time'] = time();
        DB::table($table)->where($prefix . 'id', $existing->{$prefix . 'id'})->update($update);

        return true;
    }

    protected function normalizeIds(string $ids): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn ($value) => ($id = abs((int) $value)) > 0 ? $id : null,
            explode(',', $ids)
        ))));
    }

    protected function normalizeCollectedPayload(int $mid, array $payload): ?array
    {
        $isArt = $mid === 2;
        $prefix = $isArt ? 'art_' : 'vod_';
        $nameField = $prefix . 'name';
        $enField = $prefix . 'en';
        $letterField = $prefix . 'letter';
        $contentField = $prefix . 'content';
        $blurbField = $prefix . 'blurb';
        $statusField = $prefix . 'status';
        $timeField = $prefix . 'time';
        $timeAddField = $prefix . 'time_add';
        $lockField = $prefix . 'lock';
        $rules = config('maccms.collect.' . ($isArt ? 'art' : 'vod'), []);

        foreach ($payload as $field => $value) {
            if (is_string($value) && strpos($field, '_content') === false && (!$isArt || $field !== 'vod_plot_detail')) {
                $payload[$field] = trim(strip_tags($value));
            }
        }

        $payload[$nameField] = trim((string) ($payload[$nameField] ?? ''));
        if ($payload[$nameField] === '') {
            return null;
        }

        $filterWords = array_filter(array_map('trim', explode(',', (string) ($rules['filter'] ?? ''))));
        foreach ($filterWords as $word) {
            if ($word !== '' && mb_strpos($payload[$nameField], $word) !== false) {
                return null;
            }
        }

        if (!$isArt && (string) ($rules['psename'] ?? '0') === '1' && function_exists('mac_txt_explain') && function_exists('mac_rep_pse_syn')) {
            $payload[$nameField] = mac_rep_pse_syn(mac_txt_explain((string) ($rules['namewords'] ?? ''), true), $payload[$nameField]);
        }

        if ((string) ($rules['psernd'] ?? '0') === '1' && function_exists('mac_rep_pse_rnd')) {
            $randomWords = array_values(array_filter(explode('#', (string) ($rules['words'] ?? ''))));
            $payload[$contentField] = mac_rep_pse_rnd($randomWords, (string) ($payload[$contentField] ?? ''));
        }

        if ((string) ($rules['psesyn'] ?? '0') === '1' && function_exists('mac_txt_explain') && function_exists('mac_rep_pse_syn')) {
            $payload[$contentField] = mac_rep_pse_syn(mac_txt_explain((string) ($rules['thesaurus'] ?? ''), true), (string) ($payload[$contentField] ?? ''));
        }

        if (!$isArt) {
            if ((string) ($rules['psearea'] ?? '0') === '1' && function_exists('mac_txt_explain') && function_exists('mac_rep_pse_syn')) {
                $payload['vod_area'] = mac_rep_pse_syn(mac_txt_explain((string) ($rules['areawords'] ?? ''), true), (string) ($payload['vod_area'] ?? ''));
            }
            if ((string) ($rules['pselang'] ?? '0') === '1' && function_exists('mac_txt_explain') && function_exists('mac_rep_pse_syn')) {
                $payload['vod_lang'] = mac_rep_pse_syn(mac_txt_explain((string) ($rules['langwords'] ?? ''), true), (string) ($payload['vod_lang'] ?? ''));
            }
            if ((string) ($rules['pseplayer'] ?? '0') === '1' && function_exists('mac_txt_explain') && function_exists('mac_rep_pse_syn')) {
                $payload['vod_play_from'] = mac_rep_pse_syn(mac_txt_explain((string) ($rules['playerwords'] ?? ''), true), (string) ($payload['vod_play_from'] ?? ''));
            }
            $payload['vod_class'] = $this->normalizeCsvText((string) ($payload['vod_class'] ?? ''));
            $payload['vod_actor'] = $this->normalizeCsvText((string) ($payload['vod_actor'] ?? ''));
            $payload['vod_director'] = $this->normalizeCsvText((string) ($payload['vod_director'] ?? ''));
            $payload['vod_tag'] = $this->normalizeCsvText((string) ($payload['vod_tag'] ?? ''));
            $payload['vod_plot_name'] = trim((string) ($payload['vod_plot_name'] ?? ''), '$');
            $payload['vod_plot_detail'] = trim((string) ($payload['vod_plot_detail'] ?? ''), '$');
            if (!empty($payload['vod_plot_name']) || !empty($payload['vod_plot_detail'])) {
                $payload['vod_plot'] = 1;
            }
            if (empty($payload['vod_isend']) && !empty($payload['vod_serial'])) {
                $payload['vod_isend'] = 0;
            }
        }

        $payload[$enField] = trim((string) ($payload[$enField] ?? ''));
        if ($payload[$enField] === '') {
            $payload[$enField] = Pinyin::get($payload[$nameField]);
        }
        $payload[$letterField] = strtoupper(substr((string) $payload[$enField], 0, 1));

        if (!empty($payload['type_id'])) {
            $type = DB::table('mac_type')->where('type_id', (int) $payload['type_id'])->first();
            if ($type) {
                $payload['type_id_1'] = (int) ($type->type_pid ?? 0);
            }
        }

        $payload[$lockField] = (int) ($payload[$lockField] ?? 0);
        $payload[$statusField] = isset($payload[$statusField]) && $payload[$statusField] !== ''
            ? (int) $payload[$statusField]
            : (int) ($rules['status'] ?? 1);
        $payload[$timeAddField] = $this->normalizeTimestamp($payload[$timeAddField] ?? null) ?? time();

        $timeUpdateField = $prefix . 'time_update';
        $updateTime = $this->normalizeTimestamp($payload[$timeUpdateField] ?? null);
        $payload[$timeField] = $updateTime ?? time();

        foreach ([$prefix . 'level', $prefix . 'hits', $prefix . 'hits_day', $prefix . 'hits_week', $prefix . 'hits_month', $prefix . 'up', $prefix . 'down'] as $intField) {
            if (isset($payload[$intField])) {
                $payload[$intField] = (int) $payload[$intField];
            }
        }

        if ($isArt) {
            if (empty($payload[$blurbField]) && !empty($payload[$contentField])) {
                $payload[$blurbField] = mb_substr(trim(strip_tags(str_replace('$$$', '', (string) $payload[$contentField]))), 0, 100);
            }
        } else {
            $payload['vod_year'] = isset($payload['vod_year']) ? (int) $payload['vod_year'] : 0;
            $payload['vod_total'] = isset($payload['vod_total']) ? (int) $payload['vod_total'] : 0;
            $payload['vod_serial'] = isset($payload['vod_serial']) ? (int) $payload['vod_serial'] : 0;
            $payload['vod_isend'] = isset($payload['vod_isend']) ? (int) $payload['vod_isend'] : 1;
            if (empty($payload[$blurbField]) && !empty($payload[$contentField])) {
                $payload[$blurbField] = mb_substr(trim(strip_tags((string) $payload[$contentField])), 0, 100);
            }
        }

        return $payload;
    }

    protected function getInterfaceTypeMap(int $mid): array
    {
        $raw = (string) config('maccms.interface.' . ($mid === 2 ? 'arttype' : 'vodtype'), '');
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);
        $lines = array_filter(array_map('trim', preg_split('/[\n#]+/', $raw)));
        $typeNames = DB::table('mac_type')
            ->where('type_mid', $mid)
            ->pluck('type_id', 'type_name')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $map = [];
        foreach ($lines as $line) {
            if (!str_contains($line, '=')) {
                continue;
            }
            [$localTypeName, $sourceTypeName] = array_map('trim', explode('=', $line, 2));
            if ($localTypeName === '' || $sourceTypeName === '') {
                continue;
            }
            if (isset($typeNames[$localTypeName])) {
                $map[$sourceTypeName] = $typeNames[$localTypeName];
            }
        }

        return $map;
    }

    protected function resolveCollectedTypeId($value, int $mid, array $interfaceTypeMap = []): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric((string) $value)) {
            $typeId = (int) $value;
            return DB::table('mac_type')->where('type_mid', $mid)->where('type_id', $typeId)->exists() ? $typeId : 0;
        }

        $typeName = trim((string) $value);
        if ($typeName === '') {
            return 0;
        }

        if (isset($interfaceTypeMap[$typeName])) {
            return (int) $interfaceTypeMap[$typeName];
        }

        $type = DB::table('mac_type')
            ->where('type_mid', $mid)
            ->where('type_name', $typeName)
            ->first();

        return $type ? (int) $type->type_id : 0;
    }

    protected function normalizeTimestamp($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric((string) $value)) {
            $timestamp = (int) $value;
            return strlen((string) $timestamp) >= 10 ? $timestamp : null;
        }

        $parsed = strtotime((string) $value);
        return $parsed !== false ? $parsed : null;
    }

    protected function normalizeCsvText(string $value): string
    {
        $value = str_replace(['/', '|', ';', '，', '、'], ',', strip_tags($value));
        $parts = array_filter(array_map('trim', explode(',', $value)));

        return implode(',', array_values(array_unique($parts)));
    }

    protected function findExistingCollectedRecord(int $mid, string $table, array $data, array $rules): ?object
    {
        $prefix = $mid === 2 ? 'art_' : 'vod_';
        $inrule = ',' . (string) ($rules['inrule'] ?? '') . ',';
        $query = DB::table($table);

        if ($mid === 2) {
            $query->where('art_name', $data['art_name']);
            if (str_contains($inrule, 'b') && !empty($data['type_id'])) {
                $query->where('type_id', (int) $data['type_id']);
            }

            return $query->first();
        }

        $hasRule = false;
        if (str_contains($inrule, 'a') && !empty($data['vod_name'])) {
            $query->where('vod_name', $data['vod_name']);
            $hasRule = true;
        }
        if (str_contains($inrule, 'b') && !empty($data['type_id'])) {
            $query->where('type_id', (int) $data['type_id']);
            $hasRule = true;
        }
        if (str_contains($inrule, 'c') && !empty($data['vod_year'])) {
            $query->where('vod_year', (int) $data['vod_year']);
            $hasRule = true;
        }
        if (str_contains($inrule, 'd') && !empty($data['vod_area'])) {
            $query->where('vod_area', $data['vod_area']);
            $hasRule = true;
        }
        if (str_contains($inrule, 'e') && !empty($data['vod_lang'])) {
            $query->where('vod_lang', $data['vod_lang']);
            $hasRule = true;
        }
        if (str_contains($inrule, 'h') && !empty($data['vod_douban_id'])) {
            $query->where('vod_douban_id', (int) $data['vod_douban_id']);
            $hasRule = true;
        }

        $actorKeywords = str_contains($inrule, 'f') ? $this->splitCsvText((string) ($data['vod_actor'] ?? '')) : [];
        $directorKeywords = str_contains($inrule, 'g') ? $this->splitCsvText((string) ($data['vod_director'] ?? '')) : [];

        if (!empty($actorKeywords) && !empty($directorKeywords)) {
            $query->where(function ($builder) use ($actorKeywords, $directorKeywords) {
                $builder->whereIn('vod_director', $directorKeywords);
                foreach ($actorKeywords as $keyword) {
                    $builder->orWhere('vod_actor', 'like', '%' . $keyword . '%');
                }
            });
            $hasRule = true;
        } elseif (!empty($actorKeywords)) {
            $query->where(function ($builder) use ($actorKeywords) {
                foreach ($actorKeywords as $keyword) {
                    $builder->orWhere('vod_actor', 'like', '%' . $keyword . '%');
                }
            });
            $hasRule = true;
        } elseif (!empty($directorKeywords)) {
            $query->whereIn('vod_director', $directorKeywords);
            $hasRule = true;
        }

        if (!$hasRule) {
            $query->where('vod_name', $data['vod_name']);
        }

        return $query->first();
    }

    protected function buildCollectedUpdateData(int $mid, array $existing, array $data, array $rules): array
    {
        $uprule = ',' . (string) ($rules['uprule'] ?? '') . ',';
        if ($uprule === ',,') {
            return [];
        }

        if ($mid === 2) {
            return $this->buildArtUpdateData($existing, $data, $uprule);
        }

        return $this->buildVodUpdateData($existing, $data, $uprule, (int) ($rules['urlrole'] ?? 0));
    }

    protected function buildArtUpdateData(array $existing, array $data, string $uprule): array
    {
        $update = [];

        if (str_contains($uprule, 'a') && !empty($data['art_content']) && $data['art_content'] !== ($existing['art_content'] ?? null)) {
            $update['art_content'] = $data['art_content'];
        }
        if (str_contains($uprule, 'b') && !empty($data['art_author']) && $data['art_author'] !== ($existing['art_author'] ?? null)) {
            $update['art_author'] = $data['art_author'];
        }
        if (str_contains($uprule, 'c') && !empty($data['art_from']) && $data['art_from'] !== ($existing['art_from'] ?? null)) {
            $update['art_from'] = $data['art_from'];
        }
        if (str_contains($uprule, 'd') && $this->shouldUpdateImage((string) ($existing['art_pic'] ?? ''), (string) ($data['art_pic'] ?? ''))) {
            $update['art_pic'] = $data['art_pic'];
        }
        if (str_contains($uprule, 'e') && !empty($data['art_tag']) && $data['art_tag'] !== ($existing['art_tag'] ?? null)) {
            $update['art_tag'] = $data['art_tag'];
        }
        if (str_contains($uprule, 'f') && !empty($data['art_blurb']) && $data['art_blurb'] !== ($existing['art_blurb'] ?? null)) {
            $update['art_blurb'] = $data['art_blurb'];
        }

        return $update;
    }

    protected function buildVodUpdateData(array $existing, array $data, string $uprule, int $urlRole): array
    {
        $update = [];

        if (str_contains($uprule, 'a') && !empty($data['vod_play_from'])) {
            $merged = $this->mergeVodResourceGroups(
                (string) ($existing['vod_play_from'] ?? ''),
                (string) ($existing['vod_play_url'] ?? ''),
                (string) ($existing['vod_play_server'] ?? ''),
                (string) ($existing['vod_play_note'] ?? ''),
                (string) ($data['vod_play_from'] ?? ''),
                (string) ($data['vod_play_url'] ?? ''),
                (string) ($data['vod_play_server'] ?? ''),
                (string) ($data['vod_play_note'] ?? ''),
                $urlRole
            );
            $update = array_merge($update, $merged['changed'] ? [
                'vod_play_from' => $merged['from'],
                'vod_play_url' => $merged['url'],
                'vod_play_server' => $merged['server'],
                'vod_play_note' => $merged['note'],
            ] : []);
        }

        if (str_contains($uprule, 'b') && !empty($data['vod_down_from'])) {
            $merged = $this->mergeVodResourceGroups(
                (string) ($existing['vod_down_from'] ?? ''),
                (string) ($existing['vod_down_url'] ?? ''),
                (string) ($existing['vod_down_server'] ?? ''),
                (string) ($existing['vod_down_note'] ?? ''),
                (string) ($data['vod_down_from'] ?? ''),
                (string) ($data['vod_down_url'] ?? ''),
                (string) ($data['vod_down_server'] ?? ''),
                (string) ($data['vod_down_note'] ?? ''),
                $urlRole
            );
            $update = array_merge($update, $merged['changed'] ? [
                'vod_down_from' => $merged['from'],
                'vod_down_url' => $merged['url'],
                'vod_down_server' => $merged['server'],
                'vod_down_note' => $merged['note'],
            ] : []);
        }

        $fieldMap = [
            'c' => 'vod_serial',
            'd' => 'vod_remarks',
            'e' => 'vod_director',
            'f' => 'vod_actor',
            'g' => 'vod_year',
            'h' => 'vod_area',
            'i' => 'vod_lang',
            'k' => 'vod_content',
            'l' => 'vod_tag',
            'm' => 'vod_sub',
            'o' => 'vod_writer',
            'p' => 'vod_version',
            'q' => 'vod_state',
            'r' => 'vod_blurb',
            's' => 'vod_tv',
            't' => 'vod_weekday',
            'u' => 'vod_total',
            'v' => 'vod_isend',
        ];

        foreach ($fieldMap as $rule => $field) {
            if (!str_contains($uprule, $rule) || !array_key_exists($field, $data)) {
                continue;
            }
            if ($data[$field] === '' || $data[$field] === null) {
                continue;
            }
            if (($existing[$field] ?? null) === $data[$field]) {
                continue;
            }

            if ($field === 'vod_serial' && is_numeric((string) $data[$field]) && is_numeric((string) ($existing[$field] ?? null))) {
                $update[$field] = max((int) $data[$field], (int) $existing[$field]);
                continue;
            }

            $update[$field] = $data[$field];
        }

        if (str_contains($uprule, 'j') && $this->shouldUpdateImage((string) ($existing['vod_pic'] ?? ''), (string) ($data['vod_pic'] ?? ''))) {
            $update['vod_pic'] = $data['vod_pic'];
        }

        if (str_contains($uprule, 'n') && !empty($data['vod_class']) && $data['vod_class'] !== ($existing['vod_class'] ?? null)) {
            $update['vod_class'] = $this->mergeCsvText((string) ($existing['vod_class'] ?? ''), (string) $data['vod_class']);
        }

        if (str_contains($uprule, 'w') && !empty($data['vod_plot_name']) && $data['vod_plot_name'] !== ($existing['vod_plot_name'] ?? null)) {
            $update['vod_plot'] = 1;
            $update['vod_plot_name'] = $data['vod_plot_name'];
            $update['vod_plot_detail'] = $data['vod_plot_detail'] ?? '';
        }

        return $update;
    }

    protected function mergeVodResourceGroups(
        string $oldFrom,
        string $oldUrl,
        string $oldServer,
        string $oldNote,
        string $newFrom,
        string $newUrl,
        string $newServer,
        string $newNote,
        int $urlRole
    ): array {
        $oldGroups = $this->buildVodGroupMap($oldFrom, $oldUrl, $oldServer, $oldNote);
        $newGroups = $this->buildVodGroupMap($newFrom, $newUrl, $newServer, $newNote);
        $changed = false;

        foreach ($newGroups as $group => $item) {
            if (!isset($oldGroups[$group])) {
                $oldGroups[$group] = $item;
                $changed = true;
                continue;
            }

            if ($oldGroups[$group]['url'] === $item['url']) {
                continue;
            }

            $oldGroups[$group]['url'] = $urlRole === 1
                ? implode('#', array_values(array_unique(array_filter(array_merge(
                    explode('#', $oldGroups[$group]['url']),
                    explode('#', $item['url'])
                )))))
                : $item['url'];
            $oldGroups[$group]['server'] = $item['server'] !== '' ? $item['server'] : $oldGroups[$group]['server'];
            $oldGroups[$group]['note'] = $item['note'] !== '' ? $item['note'] : $oldGroups[$group]['note'];
            $changed = true;
        }

        return [
            'changed' => $changed,
            'from' => implode('$$$', array_keys($oldGroups)),
            'url' => implode('$$$', array_column($oldGroups, 'url')),
            'server' => implode('$$$', array_column($oldGroups, 'server')),
            'note' => implode('$$$', array_column($oldGroups, 'note')),
        ];
    }

    protected function buildVodGroupMap(string $from, string $url, string $server, string $note): array
    {
        $fromArr = explode('$$$', $from);
        $urlArr = explode('$$$', $url);
        $serverArr = explode('$$$', $server);
        $noteArr = explode('$$$', $note);
        $groups = [];

        foreach ($fromArr as $index => $group) {
            $group = trim($group);
            if ($group === '') {
                continue;
            }
            $groups[$group] = [
                'url' => trim((string) ($urlArr[$index] ?? ''), '#'),
                'server' => (string) ($serverArr[$index] ?? ''),
                'note' => (string) ($noteArr[$index] ?? ''),
            ];
        }

        return $groups;
    }

    protected function shouldUpdateImage(string $oldImage, string $newImage): bool
    {
        if ($newImage === '' || $newImage === $oldImage) {
            return false;
        }

        return $oldImage === '' || str_starts_with($oldImage, 'http') || str_contains($oldImage, '#err');
    }

    protected function mergeCsvText(string $oldValue, string $newValue): string
    {
        return implode(',', array_values(array_unique(array_merge(
            $this->splitCsvText($oldValue),
            $this->splitCsvText($newValue)
        ))));
    }

    protected function splitCsvText(string $value): array
    {
        $value = $this->normalizeCsvText($value);
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    protected function applyVodCollectFilter(array $data, array $options, string $stage): array
    {
        $filter = (int) ($options['filter'] ?? 0);
        $filterFrom = $this->splitCsvText((string) ($options['filter_from'] ?? ''));
        if ($filter < 1 || empty($filterFrom)) {
            return $data;
        }

        $enabled = ($stage === 'add' && in_array($filter, [1, 2], true))
            || ($stage === 'update' && in_array($filter, [1, 3], true));
        if (!$enabled) {
            return $data;
        }

        foreach (['play', 'down'] as $kind) {
            $fromField = 'vod_' . $kind . '_from';
            $urlField = 'vod_' . $kind . '_url';
            $serverField = 'vod_' . $kind . '_server';
            $noteField = 'vod_' . $kind . '_note';
            $groups = $this->buildVodGroupMap(
                (string) ($data[$fromField] ?? ''),
                (string) ($data[$urlField] ?? ''),
                (string) ($data[$serverField] ?? ''),
                (string) ($data[$noteField] ?? '')
            );

            $groups = array_filter(
                $groups,
                static fn ($groupName) => in_array($groupName, $filterFrom, true),
                ARRAY_FILTER_USE_KEY
            );

            $data[$fromField] = implode('$$$', array_keys($groups));
            $data[$urlField] = implode('$$$', array_column($groups, 'url'));
            $data[$serverField] = implode('$$$', array_column($groups, 'server'));
            $data[$noteField] = implode('$$$', array_column($groups, 'note'));
        }

        return $data;
    }
}

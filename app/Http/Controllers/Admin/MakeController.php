<?php

namespace App\Http\Controllers\Admin;

use App\Models\Art;
use App\Models\Topic;
use App\Models\Type;
use App\Models\Vod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MakeController extends BaseController
{
    public function opt(Request $request)
    {
        $typeList = Type::where('type_status', 1)->orderBy('type_sort', 'asc')->get();

        $vodTypeList = $typeList->where('type_mid', 1)->values();
        $artTypeList = $typeList->where('type_mid', 2)->values();
        $vodTypeIds = $vodTypeList->pluck('type_id')->implode(',');
        $artTypeIds = $artTypeList->pluck('type_id')->implode(',');

        $today = strtotime(date('Y-m-d'));
        $vodTypeIdsToday = Vod::where('vod_status', 1)
            ->where('vod_time', '>=', $today)
            ->distinct()
            ->pluck('type_id')
            ->implode(',');

        $artTypeIdsToday = Art::where('art_status', 1)
            ->where('art_time', '>=', $today)
            ->distinct()
            ->pluck('type_id')
            ->implode(',');

        $topicList = Topic::where('topic_status', 1)
            ->orderBy('topic_id', 'desc')
            ->take(999)
            ->get();
        $topicIds = $topicList->pluck('topic_id')->implode(',');

        $labelList = $this->getLabelList();
        $labelIds = implode(',', $labelList);

        return view('admin.make.opt', compact(
            'vodTypeList',
            'artTypeList',
            'vodTypeIds',
            'artTypeIds',
            'vodTypeIdsToday',
            'artTypeIdsToday',
            'topicList',
            'topicIds',
            'labelList',
            'labelIds'
        ));
    }

    public function makeIndex(Request $request)
    {
        $results = [
            $this->renderToStatic('/', 'index.html'),
        ];

        return $this->renderResult('首页静态页生成结果', $results);
    }

    public function makeType(Request $request)
    {
        $tab = $request->input('tab', 'vod');
        if (!in_array($tab, ['vod', 'art'], true)) {
            return $this->renderResult('分类页生成结果', [
                $this->failedResult('', '', '无效的分类模型类型'),
            ], 422);
        }

        $ids = $this->resolveTypeIds($request, $tab);
        if (empty($ids)) {
            return $this->renderResult('分类页生成结果', [
                $this->failedResult('', '', '未选择要生成的分类'),
            ], 422);
        }

        $results = $this->generateTypePages($tab, $ids);

        return $this->renderResult(($tab === 'vod' ? '视频' : '文章') . '分类页生成结果', $results);
    }

    public function makeDetail(Request $request)
    {
        $tab = $request->input('tab', 'vod');
        if (!in_array($tab, ['vod', 'art'], true)) {
            return $this->renderResult('内容页生成结果', [
                $this->failedResult('', '', '无效的内容模型类型'),
            ], 422);
        }

        $results = $this->generateDetailPages($request, $tab);

        if ($request->boolean('with_type')) {
            $typeIds = $this->resolveTypeIds($request, $tab);
            if (!empty($typeIds)) {
                $results = array_merge($results, $this->generateTypePages($tab, $typeIds));
            }
        }

        return $this->renderResult(($tab === 'vod' ? '视频' : '文章') . '内容页生成结果', $results);
    }

    public function makeTopic(Request $request)
    {
        $scope = $request->input('scope', 'selected');
        $results = [];

        if (in_array($scope, ['index', 'all'], true)) {
            $pageCount = max((int) ceil(Topic::where('topic_status', 1)->count() / 20), 1);
            for ($page = 1; $page <= $pageCount; $page++) {
                $uri = '/topic' . ($page > 1 ? '?page=' . $page : '');
                $target = $page > 1 ? 'topic_' . $page . '.html' : 'topic.html';
                $results[] = $this->renderToStatic($uri, $target);
            }
        }

        if ($scope !== 'index') {
            $ids = $this->resolveIds($request->input('topic'));
            if ($scope === 'all' || empty($ids)) {
                $ids = Topic::where('topic_status', 1)->orderBy('topic_id')->pluck('topic_id')->all();
            }

            foreach ($ids as $id) {
                $results[] = $this->renderToStatic('/topic/detail/' . $id, 'topicdetail/' . $id . '.html');
            }

            if (!empty($ids)) {
                Topic::whereIn('topic_id', $ids)->update(['topic_time_make' => time()]);
            }
        }

        if (empty($results)) {
            $results[] = $this->failedResult('', '', '未选择要生成的专题');
        }

        return $this->renderResult('专题页生成结果', $results);
    }

    public function makeRss(Request $request)
    {
        $feed = $request->input('feed', 'index');
        $allowed = ['index', 'baidu', 'google', 'so', 'sogou', 'bing', 'sm'];
        if (!in_array($feed, $allowed, true)) {
            return $this->renderResult('RSS 生成结果', [
                $this->failedResult('', '', '无效的 RSS 类型'),
            ], 422);
        }

        $pages = max((int) $request->input('ps', 1), 1);
        $results = [];

        for ($page = 1; $page <= $pages; $page++) {
            $uri = $feed === 'index' ? '/rss' : '/rss/' . $feed;
            if ($page > 1) {
                $uri .= '?page=' . $page;
            }

            $target = 'rss/' . $feed . ($page > 1 ? '_' . $page : '') . '.xml';
            $results[] = $this->renderToStatic($uri, $target);
        }

        return $this->renderResult('RSS 生成结果', $results);
    }

    public function makeMap(Request $request)
    {
        $results = [
            $this->renderToStatic('/map', 'map.html'),
        ];

        return $this->renderResult('网站地图生成结果', $results);
    }

    public function makeLabel(Request $request)
    {
        $labels = $this->resolveLabelNames($request->input('label'));
        if (empty($labels)) {
            return $this->renderResult('自定义页面生成结果', [
                $this->failedResult('', '', '未选择要生成的自定义页面'),
            ], 422);
        }

        $results = [];
        foreach ($labels as $label) {
            $results[] = $this->renderToStatic('/label?file=' . urlencode($label), 'label/' . $label . '.html');
        }

        return $this->renderResult('自定义页面生成结果', $results);
    }

    protected function generateTypePages(string $tab, array $ids): array
    {
        $results = [];
        $pageSize = $tab === 'vod' ? 24 : 20;

        foreach ($ids as $id) {
            $count = $tab === 'vod'
                ? Vod::where('vod_status', 1)->where('type_id', $id)->count()
                : Art::where('art_status', 1)->where('type_id', $id)->count();

            $pageCount = max((int) ceil($count / $pageSize), 1);
            for ($page = 1; $page <= $pageCount; $page++) {
                $uri = '/' . $tab . '/type/' . $id . ($page > 1 ? '?page=' . $page : '');
                $target = $tab . 'type/' . $id . ($page > 1 ? '_' . $page : '') . '.html';
                $results[] = $this->renderToStatic($uri, $target);
            }
        }

        return $results;
    }

    protected function generateDetailPages(Request $request, string $tab): array
    {
        $primaryKey = $tab . '_id';
        $timeField = $tab . '_time';
        $timeMakeField = $tab . '_time_make';
        $typeInput = $tab . 'type';
        $scope = $request->input('scope', 'selected');
        $today = strtotime(date('Y-m-d'));
        $model = $tab === 'vod' ? new Vod() : new Art();
        $query = $model->newQuery()->where($tab . '_status', 1);
        $explicitIds = $this->resolveIds($request->input('ids'));
        $typeIds = $this->resolveTypeIds($request, $tab);

        if (!empty($explicitIds)) {
            $query->whereIn($primaryKey, $explicitIds);
        } else {
            if ($scope === 'selected' && empty($typeIds)) {
                return [
                    $this->failedResult('', '', '未选择要生成的内容或分类'),
                ];
            }

            if (!empty($typeIds) && in_array($scope, ['selected', 'today', 'today_then_type'], true)) {
                $query->whereIn('type_id', $typeIds);
            }

            if ($scope === 'today' || $scope === 'today_then_type') {
                $query->where($timeField, '>=', $today);
            }

            if ($scope === 'nomake') {
                $query->where(function ($builder) use ($timeField, $timeMakeField) {
                    $builder->whereNull($timeMakeField)
                        ->orWhereColumn($timeMakeField, '<', $timeField);
                });
            }

            if ($scope === 'all') {
                // no extra filters
            }
        }

        $records = $query->orderByDesc($primaryKey)->get([$primaryKey]);
        if ($records->isEmpty()) {
            return [
                $this->failedResult('', '', '没有匹配到需要生成的内容'),
            ];
        }

        $results = [];
        $madeIds = [];

        foreach ($records as $record) {
            $id = (int) $record->{$primaryKey};
            $results[] = $this->renderToStatic('/' . $tab . '/detail/' . $id, $tab . 'detail/' . $id . '.html');
            $madeIds[] = $id;
        }

        if (!empty($madeIds)) {
            $model->newQuery()->whereIn($primaryKey, $madeIds)->update([$timeMakeField => time()]);
        }

        if (($scope === 'today' || $scope === 'today_then_type') && empty($typeIds)) {
            $typeIds = $model->newQuery()
                ->where($tab . '_status', 1)
                ->where($timeField, '>=', $today)
                ->distinct()
                ->pluck('type_id')
                ->all();
            $request->merge([$typeInput => $typeIds]);
        }

        return $results;
    }

    protected function resolveTypeIds(Request $request, string $tab): array
    {
        $scope = $request->input('scope', 'selected');
        $field = $tab . 'type';
        $ids = $this->resolveIds($request->input($field));
        $today = strtotime(date('Y-m-d'));

        if ($scope === 'all') {
            return Type::where('type_status', 1)
                ->where('type_mid', $tab === 'vod' ? 1 : 2)
                ->orderBy('type_sort')
                ->pluck('type_id')
                ->all();
        }

        if (in_array($scope, ['today', 'today_then_type'], true) && empty($ids)) {
            $model = $tab === 'vod' ? new Vod() : new Art();
            return $model->newQuery()
                ->where($tab . '_status', 1)
                ->where($tab . '_time', '>=', $today)
                ->distinct()
                ->pluck('type_id')
                ->all();
        }

        return $ids;
    }

    protected function resolveIds($value): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        return collect((array) $value)
            ->map(static fn ($item) => trim((string) $item))
            ->filter(static fn ($item) => $item !== '')
            ->map(static fn ($item) => (int) $item)
            ->filter(static fn ($item) => $item > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function resolveLabelNames($value): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        return collect((array) $value)
            ->map(static fn ($item) => trim((string) $item))
            ->filter(static fn ($item) => $item !== '')
            ->map(static fn ($item) => preg_replace('/\.blade\.php$/', '', $item))
            ->map(static fn ($item) => preg_replace('/\.php$/', '', $item))
            ->map(static fn ($item) => preg_replace('/[^A-Za-z0-9_-]/', '', $item))
            ->filter(static fn ($item) => $item !== '')
            ->unique()
            ->values()
            ->all();
    }

    protected function renderToStatic(string $uri, string $targetFile, int $maxRedirects = 2): array
    {
        $request = Request::create($uri, 'GET');
        $response = app()->handle($request);
        $status = $response->getStatusCode();

        if ($status >= 300 && $status < 400 && $maxRedirects > 0) {
            $location = $response->headers->get('Location');
            if (!empty($location)) {
                $parts = parse_url($location);
                $redirectUri = ($parts['path'] ?? '/') . (!empty($parts['query']) ? '?' . $parts['query'] : '');

                return $this->renderToStatic($redirectUri, $targetFile, $maxRedirects - 1);
            }
        }

        if ($status !== 200) {
            return $this->failedResult($uri, $targetFile, '渲染失败，HTTP 状态码：' . $status);
        }

        try {
            $fullPath = public_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($targetFile, '/\\')));
            File::ensureDirectoryExists(dirname($fullPath));
            file_put_contents($fullPath, $response->getContent());

            return [
                'ok' => true,
                'source' => $uri,
                'target' => str_replace('\\', '/', $targetFile),
                'message' => '生成成功',
            ];
        } catch (\Throwable $e) {
            return $this->failedResult($uri, $targetFile, $e->getMessage());
        }
    }

    protected function failedResult(string $uri, string $targetFile, string $message): array
    {
        return [
            'ok' => false,
            'source' => $uri,
            'target' => str_replace('\\', '/', $targetFile),
            'message' => $message,
        ];
    }

    protected function renderResult(string $title, array $results, int $status = 200)
    {
        $successCount = collect($results)->where('ok', true)->count();
        $failedCount = count($results) - $successCount;

        return response()->view('admin.make.result', [
            'title' => $title,
            'results' => $results,
            'successCount' => $successCount,
            'failedCount' => $failedCount,
            'backUrl' => route('admin.make.opt'),
        ], $status);
    }

    protected function getLabelList(): array
    {
        $path = resource_path('views/label');
        if (!is_dir($path)) {
            return [];
        }

        return collect(File::files($path))
            ->filter(static fn ($file) => in_array($file->getExtension(), ['php'], true))
            ->map(static fn ($file) => preg_replace('/\.blade\.php$/', '', $file->getFilename()))
            ->map(static fn ($file) => preg_replace('/\.php$/', '', $file))
            ->sort()
            ->values()
            ->all();
    }
}

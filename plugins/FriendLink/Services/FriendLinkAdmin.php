<?php

namespace Plugins\FriendLink\Services;

use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;
use Plugins\FriendLink\Models\FriendLink;
use Plugins\FriendLink\Models\FriendLinkCate;
use Plugins\FriendLink\Models\FriendLinkClick;
use Plugins\FriendLink\Models\FriendLinkHit;

class FriendLinkAdmin
{
    public function __construct(private readonly FriendLinkService $links) {}

    /** @param  array<string, mixed>  $payload */
    public function boardPayload(array $payload): array
    {
        $payload['cates'] = $this->cateOptions();
        $payload['options'] = $this->links->options();

        return $payload;
    }

    /** @return list<array{id:int,name:string,status:int}> */
    public function cateOptions(): array
    {
        if (! Schema::hasTable('plugin_friend_link_cates')) {
            return [];
        }

        return FriendLinkCate::query()
            ->orderByDesc('sort')
            ->orderBy('id')
            ->get(['id', 'name', 'status'])
            ->map(static fn (FriendLinkCate $row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'status' => (int) $row->status,
            ])
            ->all();
    }

    /** @param  array<string, mixed>  $params */
    public function lists(array $params): array
    {
        if (! $this->links->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $desk = $this->desk($params);
        if ($desk === 'settings') {
            $opt = $this->links->options();

            return Result::success(AdminPage::slice([[
                'id' => 0,
                'mode' => $opt['mode'],
                'min_referer' => $opt['min_referer'],
                'allow_apply' => $opt['allow_apply'],
                'allow_edit' => $opt['allow_edit'],
            ]], $params));
        }
        if ($desk === 'stats') {
            return Result::success($this->statsPage($params));
        }
        if ($desk === 'cates') {
            $q = FriendLinkCate::query()->orderByDesc('sort')->orderByDesc('id');
            $kw = trim((string) ($params['q'] ?? ''));
            if ($kw !== '') {
                $q->where('name', 'like', '%'.$kw.'%');
            }

            return Result::success($this->page($q, $params, static function (FriendLinkCate $row): array {
                return $row->toArray();
            }));
        }
        if ($desk === 'clicks') {
            if (! Schema::hasTable('plugin_friend_link_clicks')) {
                return Result::success(AdminPage::slice([], $params));
            }
            $q = FriendLinkClick::query()->orderByDesc('id');
            $kw = trim((string) ($params['q'] ?? ''));
            if ($kw !== '') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('ip', 'like', '%'.$kw.'%')->orWhere('ua', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('link_id', (int) $kw);
                    }
                });
            }

            return Result::success($this->page($q, $params, function (FriendLinkClick $row): array {
                $arr = $row->toArray();
                $arr['link_name'] = $this->linkName((int) $row->link_id);

                return $arr;
            }));
        }
        if ($desk === 'hits') {
            if (! Schema::hasTable('plugin_friend_link_hits')) {
                return Result::success(AdminPage::slice([], $params));
            }
            $q = FriendLinkHit::query()->orderByDesc('id');
            $kw = trim((string) ($params['q'] ?? ''));
            if ($kw !== '') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('from_host', 'like', '%'.$kw.'%')
                        ->orWhere('from_url', 'like', '%'.$kw.'%')
                        ->orWhere('ip', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('link_id', (int) $kw);
                    }
                });
            }

            return Result::success($this->page($q, $params, function (FriendLinkHit $row): array {
                $arr = $row->toArray();
                $arr['link_name'] = $this->linkName((int) $row->link_id);

                return $arr;
            }));
        }

        $q = FriendLink::query();
        if ($desk === 'pending') {
            $q->where('status', 0);
        }
        $kw = trim((string) ($params['q'] ?? $params['name'] ?? ''));
        if ($kw !== '') {
            $q->where(function ($inner) use ($kw) {
                $inner->where('name', 'like', '%'.$kw.'%')->orWhere('url', 'like', '%'.$kw.'%');
            });
        }
        if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null && $desk !== 'pending') {
            $q->where('status', (int) $params['status']);
        }
        if (array_key_exists('cate_id', $params) && $params['cate_id'] !== '' && $params['cate_id'] !== null) {
            $q->where('cate_id', (int) $params['cate_id']);
        }
        $opt = $this->links->options();
        if ($opt['mode'] === 'strong') {
            $q->orderByDesc('referer_total')->orderByDesc('sort')->orderByDesc('id');
        } else {
            $q->orderByDesc('sort')->orderByDesc('id');
        }
        $cates = [];
        foreach ($this->cateOptions() as $cate) {
            $cates[(int) $cate['id']] = (string) $cate['name'];
        }

        return Result::success($this->page($q, $params, static function (FriendLink $row) use ($cates): array {
            $arr = $row->toArray();
            unset($arr['edit_token']);
            $arr['cate_name'] = (string) ($cates[(int) $row->cate_id] ?? '');
            $arr['status_label'] = match ((int) $row->status) {
                1 => '显示',
                2 => '拒绝',
                3 => '冻结',
                default => '待审',
            };
            $arr['type_label'] = (string) $row->type === 'image' ? '图片' : '文字';
            $arr['go_url'] = '/links/go/'.(int) $row->id;

            return $arr;
        }));
    }

    /** @param  array<string, mixed>  $data */
    public function save(array $data, ?int $id = null): array
    {
        if (! $this->links->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $desk = $this->desk($data);
        if (in_array($desk, ['clicks', 'hits', 'stats'], true)) {
            return Result::fail('由前台产生，不能手添或改。');
        }
        if ($desk === 'settings') {
            return $this->saveSettings($data);
        }
        if ($desk === 'cates') {
            return $this->saveCate($data, $id);
        }

        return $this->saveLink($data, $id);
    }

    public function delete(int $id): array
    {
        $desk = $this->desk(request()->all());
        if (in_array($desk, ['clicks', 'hits', 'stats', 'settings'], true)) {
            return Result::fail('由前台产生，不能手添或改。');
        }
        if ($desk === 'cates') {
            $row = FriendLinkCate::query()->find($id);
            if (! $row) {
                return Result::fail('数据不存在');
            }
            $name = (string) $row->name;
            $row->delete();
            if (Schema::hasTable('plugin_friend_links')) {
                FriendLink::query()->where('cate_id', $id)->update(['cate_id' => 0]);
            }

            return AdminOpLog::ifOk(Result::success(), 'delete', '删除友链分类 '.$name, [
                'module' => 'flinks',
                'target_id' => $id,
            ]);
        }
        $row = FriendLink::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        $name = (string) $row->name;
        $row->delete();
        if (Schema::hasTable('plugin_friend_link_clicks')) {
            FriendLinkClick::query()->where('link_id', $id)->delete();
        }
        if (Schema::hasTable('plugin_friend_link_hits')) {
            FriendLinkHit::query()->where('link_id', $id)->delete();
        }

        return AdminOpLog::ifOk(Result::success(), 'delete', '删除友链 '.$name, [
            'module' => 'flinks',
            'target_id' => $id,
        ]);
    }

    /** @param  array<string, mixed>  $data */
    private function saveSettings(array $data): array
    {
        $mode = strtolower(trim((string) ($data['mode'] ?? 'normal')));
        $this->links->setOption('mode', $mode === 'strong' ? 'strong' : 'normal');
        $this->links->setOption('min_referer', (string) max(1, (int) ($data['min_referer'] ?? 1)));
        $this->links->setOption('allow_apply', (int) ($data['allow_apply'] ?? 0) === 1 ? '1' : '0');
        $this->links->setOption('allow_edit', (int) ($data['allow_edit'] ?? 0) === 1 ? '1' : '0');

        return AdminOpLog::ifOk(Result::success($this->links->options()), 'save', '保存友链设置', [
            'module' => 'flinks',
        ]);
    }

    /** @param  array<string, mixed>  $data */
    private function saveCate(array $data, ?int $id): array
    {
        $name = mb_substr(trim((string) ($data['name'] ?? '')), 0, 80);
        if ($id === null && $name === '') {
            return Result::fail('请填写名称');
        }
        $row = $id ? FriendLinkCate::query()->find($id) : new FriendLinkCate;
        if ($id && ! $row) {
            return Result::fail('数据不存在');
        }
        if ($name !== '') {
            $row->name = $name;
        }
        if (array_key_exists('sort', $data) || $id === null) {
            $row->sort = max(0, (int) ($data['sort'] ?? 0));
        }
        if (array_key_exists('status', $data) || $id === null) {
            $row->status = (int) ($data['status'] ?? 1) === 1 ? 1 : 0;
        }
        $row->save();

        return AdminOpLog::ifOk(Result::success(['id' => (int) $row->id]), 'save', '保存友链分类 '.$row->name, [
            'module' => 'flinks',
            'target_id' => (int) $row->id,
        ]);
    }

    /** @param  array<string, mixed>  $data */
    private function saveLink(array $data, ?int $id): array
    {
        $name = mb_substr(trim((string) ($data['name'] ?? '')), 0, 120);
        if ($id === null && $name === '') {
            return Result::fail('请填写名称');
        }
        $urlRaw = array_key_exists('url', $data) || $id === null ? (string) ($data['url'] ?? '') : null;
        $url = $urlRaw === null ? null : FriendLinkService::httpUrl($urlRaw);
        if ($urlRaw !== null && $url === null) {
            return Result::fail('网址只接受 http 或 https');
        }
        $row = $id ? FriendLink::query()->find($id) : new FriendLink;
        if ($id && ! $row) {
            return Result::fail('数据不存在');
        }
        $now = time();
        if ($id === null) {
            $row->edit_token = bin2hex(random_bytes(16));
            $row->clicks = 0;
            $row->referer_total = 0;
            $row->referer_day = 0;
            $row->referer_month = 0;
            $row->referer_year = 0;
            $row->day_key = '';
            $row->month_key = '';
            $row->year_key = '';
            $row->last_referer_at = 0;
            $row->member_id = 0;
            $row->created_at = $now;
            $row->status = 1;
        }
        if ($name !== '') {
            $row->name = $name;
        }
        if ($url !== null) {
            $row->url = $url;
        }
        foreach (['logo' => 500, 'email' => 120, 'remark' => 255] as $field => $max) {
            if (array_key_exists($field, $data) || $id === null) {
                $row->{$field} = mb_substr(trim((string) ($data[$field] ?? '')), 0, $max);
            }
        }
        if (array_key_exists('type', $data) || $id === null) {
            $type = strtolower(trim((string) ($data['type'] ?? 'text')));
            $row->type = in_array($type, ['text', 'image'], true) ? $type : 'text';
        }
        if (array_key_exists('cate_id', $data) || $id === null) {
            $row->cate_id = max(0, (int) ($data['cate_id'] ?? 0));
        }
        if (array_key_exists('sort', $data) || $id === null) {
            $row->sort = max(0, (int) ($data['sort'] ?? 0));
        }
        if (array_key_exists('status', $data)) {
            $st = (int) $data['status'];
            $row->status = in_array($st, [0, 1, 2, 3], true) ? $st : 0;
        }
        $row->updated_at = $now;
        $row->save();

        return AdminOpLog::ifOk(Result::success(['id' => (int) $row->id]), 'save', '保存友链 '.$row->name, [
            'module' => 'flinks',
            'target_id' => (int) $row->id,
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function statsPage(array $params): array
    {
        $period = strtolower(trim((string) ($params['period'] ?? 'day')));
        if (! in_array($period, ['day', 'month', 'year'], true)) {
            $period = 'day';
        }
        $rows = [];
        if (Schema::hasTable('plugin_friend_link_hits')) {
            $hits = FriendLinkHit::query()->orderBy('id')->get(['link_id', 'day_key']);
            $bucket = [];
            foreach ($hits as $hit) {
                $day = (string) $hit->day_key;
                $key = match ($period) {
                    'year' => substr($day, 0, 4),
                    'month' => substr($day, 0, 6),
                    default => $day,
                };
                if ($key === '') {
                    continue;
                }
                $lid = (int) $hit->link_id;
                $bucket[$key][$lid] = ($bucket[$key][$lid] ?? 0) + 1;
            }
            krsort($bucket);
            foreach ($bucket as $key => $byLink) {
                foreach ($byLink as $lid => $count) {
                    $rows[] = [
                        'id' => $key.'-'.$lid,
                        'period' => $period,
                        'period_key' => $key,
                        'link_id' => $lid,
                        'link_name' => $this->linkName($lid),
                        'hits' => $count,
                    ];
                }
            }
        }

        return AdminPage::slice($rows, $params);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $q
     * @param  array<string, mixed>  $params
     * @param  callable(\Illuminate\Database\Eloquent\Model): array<string, mixed>  $map
     * @return array{total:int,per_page:int,current_page:int,last_page:int,data:list<array<string, mixed>>}
     */
    private function page($q, array $params, callable $map): array
    {
        $limit = max(1, (int) ($params['limit'] ?? 15));
        $pageNo = max(1, (int) ($params['page'] ?? request()->input('page', 1)));
        $page = $q->paginate($limit, ['*'], 'page', $pageNo);
        $rows = [];
        foreach ($page->items() as $row) {
            $rows[] = $map($row);
        }

        return AdminPage::of($page, $rows);
    }

    /** @param  array<string, mixed>  $params */
    private function desk(array $params): string
    {
        $desk = strtolower(trim((string) ($params['desk'] ?? request()->input('desk', ''))));
        $allowed = ['links', 'pending', 'cates', 'clicks', 'hits', 'stats', 'settings'];
        if (! in_array($desk, $allowed, true)) {
            return 'links';
        }

        return $desk;
    }

    private function linkName(int $id): string
    {
        if ($id < 1 || ! Schema::hasTable('plugin_friend_links')) {
            return '';
        }
        $name = FriendLink::query()->where('id', $id)->value('name');

        return is_string($name) ? $name : '';
    }
}

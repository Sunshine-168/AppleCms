<?php

namespace Plugins\Advert\Services;

use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;
use Plugins\Advert\Models\PluginAd;
use Plugins\Advert\Models\PluginAdClick;
use Plugins\Advert\Models\PluginAdDay;

class AdvertAdmin
{
    public function __construct(private readonly AdvertService $ads) {}

    /** @param  array<string, mixed>  $payload */
    public function boardPayload(array $payload): array
    {
        return $payload;
    }

    /** @param  array<string, mixed>  $params */
    public function lists(array $params): array
    {
        if (! $this->ads->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $desk = $this->desk($params);
        if ($desk === 'stats') {
            return Result::success($this->statsPage($params));
        }
        if ($desk === 'clicks') {
            if (! Schema::hasTable('plugin_ad_clicks')) {
                return Result::success(AdminPage::slice([], $params));
            }
            $q = PluginAdClick::query()->orderByDesc('id');
            $kw = trim((string) ($params['q'] ?? ''));
            if ($kw !== '') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('ip', 'like', '%'.$kw.'%')->orWhere('page', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('ad_id', (int) $kw);
                    }
                });
            }

            return Result::success($this->page($q, $params, function (PluginAdClick $row): array {
                $arr = $row->toArray();
                $arr['ad_name'] = $this->adName((int) $row->ad_id);

                return $arr;
            }));
        }
        $q = PluginAd::query()->orderByDesc('sort')->orderByDesc('id');
        $kw = trim((string) ($params['q'] ?? $params['name'] ?? ''));
        if ($kw !== '') {
            $q->where(function ($inner) use ($kw) {
                $inner->where('name', 'like', '%'.$kw.'%')->orWhere('title', 'like', '%'.$kw.'%');
            });
        }
        if (array_key_exists('slot', $params) && $params['slot'] !== '' && $params['slot'] !== null) {
            $q->where('slot', (string) $params['slot']);
        }
        if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
            $q->where('status', (int) $params['status']);
        }

        return Result::success($this->page($q, $params, static function (PluginAd $row): array {
            $arr = $row->toArray();
            $arr['slot_label'] = match ((string) $row->slot) {
                'top' => admin_t('ui.slot_top'),
                'bottom' => admin_t('ui.slot_bottom_bar'),
                'player' => admin_t('ui.slot_player_bar'),
                default => admin_t('ui.slot_body'),
            };
            $arr['type_label'] = (string) $row->type === 'image' ? admin_t('ui.kind_image') : admin_t('ui.kind_text');
            $arr['status_label'] = (int) $row->status === 1 ? admin_t('ui.enabled') : admin_t('ui.disabled');
            $arr['go_url'] = '/ads/go/'.(int) $row->id;

            return $arr;
        }));
    }

    /** @param  array<string, mixed>  $data */
    public function save(array $data, ?int $id = null): array
    {
        if (! $this->ads->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $desk = $this->desk($data);
        if (in_array($desk, ['clicks', 'stats'], true)) {
            return Result::fail('由前台产生，不能手添或改。');
        }
        $name = mb_substr(trim((string) ($data['name'] ?? '')), 0, 120);
        if ($id === null && $name === '') {
            return Result::fail('请填写名称');
        }
        $urlRaw = array_key_exists('url', $data) ? trim((string) $data['url']) : null;
        $url = null;
        if ($urlRaw !== null) {
            if ($urlRaw === '') {
                $url = '';
            } else {
                $url = AdvertService::httpUrl($urlRaw);
                if ($url === null) {
                    return Result::fail('网址只接受 http 或 https');
                }
            }
        }
        $row = $id ? PluginAd::query()->find($id) : new PluginAd;
        if ($id && ! $row) {
            return Result::fail('数据不存在');
        }
        $now = time();
        if ($id === null) {
            $row->impressions = 0;
            $row->clicks = 0;
            $row->created_at = $now;
            $row->status = 1;
        }
        if ($name !== '') {
            $row->name = $name;
        }
        if (array_key_exists('type', $data) || $id === null) {
            $type = strtolower(trim((string) ($data['type'] ?? 'text')));
            $row->type = $type === 'image' ? 'image' : 'text';
        }
        if (array_key_exists('slot', $data) || $id === null) {
            $slot = strtolower(trim((string) ($data['slot'] ?? 'content')));
            $row->slot = in_array($slot, ['top', 'bottom', 'player', 'content'], true) ? $slot : 'content';
        }
        if (array_key_exists('title', $data) || $id === null) {
            $row->title = mb_substr(trim((string) ($data['title'] ?? '')), 0, 200);
        }
        if ($url !== null) {
            $row->url = $url;
        } elseif ($id === null) {
            $row->url = '';
        }
        if (array_key_exists('image', $data) || $id === null) {
            $row->image = mb_substr(trim((string) ($data['image'] ?? '')), 0, 500);
        }
        if (array_key_exists('sort', $data) || $id === null) {
            $row->sort = max(0, (int) ($data['sort'] ?? 0));
        }
        if (array_key_exists('start_at', $data) || $id === null) {
            $row->start_at = max(0, (int) ($data['start_at'] ?? 0));
        }
        if (array_key_exists('expire_at', $data) || $id === null) {
            $row->expire_at = max(0, (int) ($data['expire_at'] ?? 0));
        }
        if (array_key_exists('status', $data)) {
            $row->status = (int) $data['status'] === 1 ? 1 : 0;
        }
        $row->updated_at = $now;
        $row->save();

        return AdminOpLog::ifOk(Result::success(['id' => (int) $row->id]), 'save', '保存广告 '.$row->name, [
            'module' => 'adverts',
            'target_id' => (int) $row->id,
        ]);
    }

    public function delete(int $id): array
    {
        $desk = $this->desk(request()->all());
        if (in_array($desk, ['clicks', 'stats'], true)) {
            return Result::fail('由前台产生，不能手添或改。');
        }
        $row = PluginAd::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        $name = (string) $row->name;
        $row->delete();
        if (Schema::hasTable('plugin_ad_clicks')) {
            PluginAdClick::query()->where('ad_id', $id)->delete();
        }
        if (Schema::hasTable('plugin_ad_days')) {
            PluginAdDay::query()->where('ad_id', $id)->delete();
        }

        return AdminOpLog::ifOk(Result::success(), 'delete', '删除广告 '.$name, [
            'module' => 'adverts',
            'target_id' => $id,
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
        if (Schema::hasTable('plugin_ad_days')) {
            $days = PluginAdDay::query()->orderByDesc('day_key')->get();
            $bucket = [];
            foreach ($days as $day) {
                $key = match ($period) {
                    'year' => substr((string) $day->day_key, 0, 4),
                    'month' => substr((string) $day->day_key, 0, 6),
                    default => (string) $day->day_key,
                };
                if ($key === '') {
                    continue;
                }
                $lid = (int) $day->ad_id;
                if (! isset($bucket[$key][$lid])) {
                    $bucket[$key][$lid] = ['impressions' => 0, 'clicks' => 0];
                }
                $bucket[$key][$lid]['impressions'] += (int) $day->impressions;
                $bucket[$key][$lid]['clicks'] += (int) $day->clicks;
            }
            krsort($bucket);
            foreach ($bucket as $key => $byAd) {
                foreach ($byAd as $lid => $nums) {
                    $rows[] = [
                        'id' => $key.'-'.$lid,
                        'period' => $period,
                        'period_key' => $key,
                        'ad_id' => $lid,
                        'ad_name' => $this->adName($lid),
                        'impressions' => $nums['impressions'],
                        'clicks' => $nums['clicks'],
                    ];
                }
            }
        } elseif (Schema::hasTable('plugin_ad_clicks')) {
            $clicks = PluginAdClick::query()->orderBy('id')->get(['ad_id', 'created_at']);
            $bucket = [];
            foreach ($clicks as $click) {
                $ts = (int) $click->created_at;
                $key = match ($period) {
                    'year' => date('Y', $ts),
                    'month' => date('Ym', $ts),
                    default => date('Ymd', $ts),
                };
                $lid = (int) $click->ad_id;
                $bucket[$key][$lid] = ($bucket[$key][$lid] ?? 0) + 1;
            }
            krsort($bucket);
            foreach ($bucket as $key => $byAd) {
                foreach ($byAd as $lid => $count) {
                    $rows[] = [
                        'id' => $key.'-'.$lid,
                        'period' => $period,
                        'period_key' => $key,
                        'ad_id' => $lid,
                        'ad_name' => $this->adName($lid),
                        'impressions' => 0,
                        'clicks' => $count,
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

        return in_array($desk, ['ads', 'clicks', 'stats'], true) ? $desk : 'ads';
    }

    private function adName(int $id): string
    {
        if ($id < 1 || ! Schema::hasTable('plugin_ads')) {
            return '';
        }
        $name = PluginAd::query()->where('id', $id)->value('name');

        return is_string($name) ? $name : '';
    }
}

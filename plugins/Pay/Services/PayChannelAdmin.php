<?php

namespace Plugins\Pay\Services;

use App\Support\AdminOpLog;
use App\Support\Utils\Result;
use Plugins\Pay\Models\PayChannel;

class PayChannelAdmin
{
    public function __construct(
        private readonly PayChannelService $channels,
        private readonly PayStatsService $stats,
    ) {}

    /** @param  array<string, mixed>  $payload */
    public function boardPayload(array $payload): array
    {
        $payload['drivers'] = [
            'epay' => PayChannelService::driverLabel('epay'),
            'dfpay' => PayChannelService::driverLabel('dfpay'),
        ];
        $payload['notify_epay'] = url('/pay/notify/epay');
        $payload['notify_dfpay'] = url('/pay/notify/dfpay');
        $desk = (string) ($payload['desk'] ?? 'channels');
        if ($desk === 'stats') {
            $payload['stats'] = $this->stats->summary();
        }

        return $payload;
    }

    /** @param  array<string, mixed>  $params */
    public function lists(array $params): array
    {
        if (! $this->channels->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $q = PayChannel::query()->orderByDesc('sort')->orderByDesc('id');
        $kw = trim((string) ($params['q'] ?? $params['title'] ?? ''));
        if ($kw !== '') {
            $q->where(function ($inner) use ($kw) {
                $inner->where('title', 'like', '%'.$kw.'%')
                    ->orWhere('mch_id', 'like', '%'.$kw.'%')
                    ->orWhere('code', 'like', '%'.$kw.'%');
            });
        }
        if (in_array((string) ($params['driver'] ?? ''), ['epay', 'dfpay'], true)) {
            $q->where('driver', (string) $params['driver']);
        }
        if (in_array((string) ($params['status'] ?? ''), ['0', '1'], true)) {
            $q->where('status', (int) $params['status']);
        }
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = max(1, min(100, (int) ($params['limit'] ?? 20)));
        $total = (int) $q->count();
        $last = max(1, (int) ceil($total / $limit));
        if ($page > $last) {
            $page = $last;
        }
        $rows = $q->forPage($page, $limit)->get()
            ->map(fn (PayChannel $row) => $this->channels->present($row))
            ->all();

        return Result::success([
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => $last,
            'data' => $rows,
        ]);
    }

    /** @param  array<string, mixed>  $data */
    public function save(array $data, ?int $id = null): array
    {
        $ok = $this->channels->save($data, $id);
        if (($ok['code'] ?? 1) !== 0) {
            return $ok;
        }

        return AdminOpLog::ifOk($ok, $id ? 'update' : 'create', ($id ? '改了支付通道 ' : '加了支付通道 ').trim((string) ($data['title'] ?? '')), [
            'module' => 'pay_channels',
            'target_id' => (int) ($ok['data']['id'] ?? $id ?? 0),
        ]);
    }

    public function delete(int $id): array
    {
        $row = PayChannel::query()->find($id);
        if (! $row) {
            return Result::fail('通道不存在');
        }
        $title = (string) $row->title;
        $row->delete();

        return AdminOpLog::ifOk(Result::success(), 'delete', '删除支付通道 '.$title, [
            'module' => 'pay_channels',
            'target_id' => $id,
        ]);
    }
}

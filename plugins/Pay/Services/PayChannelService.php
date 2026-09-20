<?php

namespace Plugins\Pay\Services;

use App\Models\Member\MemberOrder;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;
use Plugins\Pay\Drivers\DfPayDriver;
use Plugins\Pay\Drivers\EpayDriver;
use Plugins\Pay\Drivers\PayDriver;
use Plugins\Pay\Models\PayChannel;

class PayChannelService
{
    public const DRIVERS = [
        'epay' => '易支付',
        'dfpay' => 'DfPay（A13 协议）',
    ];

    public static function driverLabel(string $driver): string
    {
        return match (strtolower($driver)) {
            'dfpay' => admin_t('ui.channel_dfpay'),
            'epay' => admin_t('ui.channel_epay'),
            default => $driver,
        };
    }

    public function ready(): bool
    {
        return Schema::hasTable('plugin_pay_channels');
    }

    /** @return list<array<string, mixed>> */
    public function enabledForCheckout(): array
    {
        if (! $this->ready()) {
            return [];
        }

        return PayChannel::query()
            ->where('status', 1)
            ->orderByDesc('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (PayChannel $row) => $this->present($row))
            ->all();
    }

    public function find(int $id): ?PayChannel
    {
        if ($id < 1 || ! $this->ready()) {
            return null;
        }

        return PayChannel::query()->find($id);
    }

    public function driverFor(PayChannel $channel): ?PayDriver
    {
        return match (strtolower(trim((string) $channel->driver))) {
            'epay' => app(EpayDriver::class),
            'dfpay' => app(DfPayDriver::class),
            default => null,
        };
    }

    /** @return array{code:int,msg:string,data?:array<string,mixed>} */
    public function create(MemberOrder $order, PayChannel $channel): array
    {
        $driver = $this->driverFor($channel);
        if (! $driver) {
            return Result::fail('未知支付驱动');
        }
        $min = (int) ($channel->min_fen ?? 0);
        $max = (int) ($channel->max_fen ?? 0);
        $amount = (int) $order->amount;
        if ($min > 0 && $amount < $min) {
            return Result::fail('低于该通道最低金额');
        }
        if ($max > 0 && $amount > $max) {
            return Result::fail('超过该通道最高金额');
        }

        return $driver->create($order, $channel);
    }

    /** @param  array<string, mixed>  $payload */
    public function notify(string $driver, array $payload): string
    {
        $driver = strtolower(trim($driver));
        $impl = match ($driver) {
            'epay' => app(EpayDriver::class),
            'dfpay' => app(DfPayDriver::class),
            default => null,
        };
        if (! $impl) {
            return 'fail';
        }

        return $impl->notify($payload);
    }

    /** @return array<string, mixed> */
    public function present(PayChannel $row): array
    {
        $driver = strtolower(trim((string) $row->driver));

        return [
            'id' => (int) $row->id,
            'title' => (string) $row->title,
            'driver' => $driver,
            'driver_label' => self::DRIVERS[$driver] ?? $driver,
            'code' => (string) $row->code,
            'api_url' => (string) $row->api_url,
            'mch_id' => (string) $row->mch_id,
            'app_key' => (string) $row->app_key,
            'min_fen' => (int) ($row->min_fen ?? 0),
            'max_fen' => (int) ($row->max_fen ?? 0),
            'min_yuan' => round(((int) ($row->min_fen ?? 0)) / 100, 2),
            'max_yuan' => round(((int) ($row->max_fen ?? 0)) / 100, 2),
            'sort' => (int) ($row->sort ?? 0),
            'status' => (int) ($row->status ?? 0),
            'status_label' => (int) ($row->status ?? 0) === 1 ? '启用' : '停用',
            'hint' => (string) ($row->hint ?? ''),
            'notify_url' => url('/pay/notify/'.$driver),
            'value' => 'ch:'.$row->id,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{code:int,msg:string,data?:array<string,mixed>}
     */
    public function save(array $data, ?int $id = null): array
    {
        if (! $this->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $title = mb_substr(trim((string) ($data['title'] ?? '')), 0, 80);
        if ($title === '') {
            return Result::fail('请填写通道名称');
        }
        $driver = strtolower(trim((string) ($data['driver'] ?? 'epay')));
        if (! isset(self::DRIVERS[$driver])) {
            return Result::fail('驱动只能是 epay 或 dfpay');
        }
        $apiUrl = mb_substr(trim((string) ($data['api_url'] ?? '')), 0, 255);
        $mchId = mb_substr(trim((string) ($data['mch_id'] ?? '')), 0, 80);
        $appKey = mb_substr(trim((string) ($data['app_key'] ?? '')), 0, 120);
        if ($apiUrl === '' || $mchId === '' || $appKey === '') {
            return Result::fail('请填写网关地址、商户号、密钥');
        }
        if ($driver === 'dfpay' && trim((string) ($data['code'] ?? '')) === '') {
            return Result::fail('DfPay 必须填产品码（payType）');
        }

        $now = time();
        $payload = [
            'title' => $title,
            'driver' => $driver,
            'code' => mb_substr(trim((string) ($data['code'] ?? '')), 0, 40),
            'api_url' => $apiUrl,
            'mch_id' => $mchId,
            'app_key' => $appKey,
            'min_fen' => max(0, (int) round(((float) ($data['min_yuan'] ?? 0)) * 100)),
            'max_fen' => max(0, (int) round(((float) ($data['max_yuan'] ?? 0)) * 100)),
            'sort' => max(0, (int) ($data['sort'] ?? 0)),
            'status' => (int) ($data['status'] ?? 1) === 1 ? 1 : 0,
            'hint' => mb_substr(trim((string) ($data['hint'] ?? '')), 0, 250),
            'updated_at' => $now,
        ];
        if ($id) {
            $row = PayChannel::query()->find($id);
            if (! $row) {
                return Result::fail('通道不存在');
            }
            $row->fill($payload)->save();
        } else {
            $payload['created_at'] = $now;
            $row = PayChannel::query()->create($payload);
        }

        return Result::success(['id' => (int) $row->id], $id ? '已保存' : '已创建');
    }
}

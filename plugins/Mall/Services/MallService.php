<?php

namespace Plugins\Mall\Services;

use App\Models\Member\Member;
use App\Models\Member\MemberGroup;
use App\Models\Member\MemberPointLog;
use App\Models\Video\VideoCard;
use App\Support\Utils\Result;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugins\Mall\Models\MallGood;
use Plugins\Mall\Models\MallOrder;

class MallService
{
    public function ready(): bool
    {
        return Schema::hasTable('plugin_mall_goods') && Schema::hasTable('plugin_mall_orders');
    }

    /** @return list<array{id:int,name:string}> */
    public function adminGroups(): array
    {
        if (! Schema::hasTable('member_groups')) {
            return [];
        }

        return MemberGroup::query()
            ->orderByDesc('sort')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(static fn ($row) => ['id' => (int) $row->id, 'name' => (string) $row->name])
            ->all();
    }

    public function paginate(int $perPage = 24): LengthAwarePaginator
    {
        return MallGood::query()
            ->where('status', 1)
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function published(int $id): ?MallGood
    {
        if (! $this->ready()) {
            return null;
        }

        return MallGood::query()->where('status', 1)->find($id);
    }

    public static function typeLabel(?string $type): string
    {
        return match (self::normalizeType($type)) {
            'vip' => '会员组',
            'card' => '积分卡密',
            default => '实物发货',
        };
    }

    public static function normalizeType(?string $type): string
    {
        $type = strtolower(trim((string) $type));
        if ($type === '' || $type === 'goods') {
            return 'goods';
        }

        return $type;
    }

    /** @return array<string, mixed> */
    public static function decodeExt(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        $arr = json_decode((string) $raw, true);

        return is_array($arr) ? $arr : [];
    }

    /** @return array<string, mixed> */
    public function buy(int $id, Member $member): array
    {
        if (! $this->ready()) {
            return Result::fail('商城未启用');
        }

        try {
            return DB::transaction(function () use ($id, $member) {
                $goods = MallGood::query()->where('id', $id)->lockForUpdate()->first();
                if (! $goods || (int) $goods->status !== 1) {
                    return Result::fail('商品不存在或已下架');
                }
                if ((int) $goods->stock < 1) {
                    return Result::fail('库存不足');
                }
                $cost = (int) $goods->points;
                $fresh = Member::query()->where('id', $member->id)->lockForUpdate()->first();
                if (! $fresh) {
                    return Result::fail('请先登录');
                }
                if ((int) $fresh->points < $cost) {
                    return Result::fail('积分不足');
                }

                $type = self::normalizeType(Schema::hasColumn('plugin_mall_goods', 'type') ? (string) ($goods->type ?? '') : 'goods');
                if (! in_array($type, ['goods', 'vip', 'card'], true)) {
                    return Result::fail('没有这种商品');
                }
                $ext = self::decodeExt(Schema::hasColumn('plugin_mall_goods', 'ext') ? $goods->ext : '');

                $groupId = 0;
                $assignCard = null;
                if ($type === 'vip') {
                    $groupId = (int) ($ext['group_id'] ?? 0);
                    if ($groupId < 1 || ! Schema::hasTable('member_groups') || ! MemberGroup::query()->where('id', $groupId)->exists()) {
                        return Result::fail('会员组不存在');
                    }
                }
                if ($type === 'card') {
                    if (! Schema::hasTable('video_cards')) {
                        return Result::fail('卡密表不存在');
                    }
                    $mode = strtolower(trim((string) ($ext['mode'] ?? $ext['card_mode'] ?? 'generate')));
                    if ($mode === 'assign') {
                        $assignCard = $this->lockAssignableCard($ext);
                        if (! $assignCard) {
                            return Result::fail('没有可领取的卡密');
                        }
                    }
                }

                $fresh->points = (int) $fresh->points - $cost;
                if ($type === 'vip') {
                    $fresh->group_id = $groupId;
                }
                $fresh->save();
                $goods->stock = (int) $goods->stock - 1;
                $goods->save();

                $now = time();
                $status = 1;
                $completeAt = 0;
                $delivery = '';
                $msg = '兑换成功，等待发货';
                $deliveryArr = [];

                if ($type === 'vip') {
                    $status = 2;
                    $completeAt = $now;
                    $deliveryArr = ['type' => 'vip', 'group_id' => $groupId];
                    $delivery = json_encode($deliveryArr, JSON_UNESCAPED_UNICODE) ?: '';
                    $msg = '兑换成功，会员组已更新';
                } elseif ($type === 'card') {
                    $code = $assignCard ? (string) $assignCard->code : $this->makeUniqueCard((int) ($ext['card_points'] ?? $goods->points));
                    $status = 2;
                    $completeAt = $now;
                    $deliveryArr = ['type' => 'card', 'code' => $code];
                    $delivery = json_encode($deliveryArr, JSON_UNESCAPED_UNICODE) ?: '';
                    $msg = '兑换成功，卡密 '.$code;
                }

                $orderPayload = [
                    'member_id' => (int) $fresh->id,
                    'goods_id' => (int) $goods->id,
                    'goods_name' => (string) $goods->name,
                    'points' => $cost,
                    'status' => $status,
                    'remark' => '',
                    'created_at' => $now,
                ];
                if (Schema::hasColumn('plugin_mall_orders', 'goods_type')) {
                    $orderPayload['goods_type'] = $type;
                }
                if (Schema::hasColumn('plugin_mall_orders', 'delivery')) {
                    $orderPayload['delivery'] = $delivery;
                }
                if (Schema::hasColumn('plugin_mall_orders', 'complete_at')) {
                    $orderPayload['complete_at'] = $completeAt;
                }
                $order = MallOrder::query()->create($orderPayload);
                if (Schema::hasTable('member_point_logs')) {
                    MemberPointLog::query()->create([
                        'member_id' => (int) $fresh->id,
                        'points' => -$cost,
                        'balance' => (int) $fresh->points,
                        'type' => 'mall',
                        'remark' => '兑换 '.$goods->name,
                        'created_at' => $now,
                    ]);
                }

                return Result::success([
                    'id' => $order->id,
                    'type' => $type,
                    'status' => $status,
                    'delivery' => $deliveryArr,
                ], $msg);
            });
        } catch (\RuntimeException $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : '兑换失败，请稍后再试');
        } catch (\Throwable) {
            return Result::fail('兑换失败，请稍后再试');
        }
    }

    private function lockAssignableCard(array $ext): ?VideoCard
    {
        $q = VideoCard::query()->where('status', 1)->where('used_by', 0);
        $want = (int) ($ext['card_points'] ?? 0);
        if ($want > 0) {
            $q->where('points', $want);
        }
        $taken = $this->takenCardCodes();
        if ($taken !== []) {
            $q->whereNotIn('code', $taken);
        }

        return $q->orderBy('id')->lockForUpdate()->first();
    }

    /** @return list<string> */
    private function takenCardCodes(): array
    {
        if (! Schema::hasColumn('plugin_mall_orders', 'delivery')) {
            return [];
        }
        $codes = [];
        foreach (MallOrder::query()->where('goods_type', 'card')->where('delivery', '!=', '')->get(['delivery']) as $row) {
            $data = self::decodeExt($row->delivery);
            $code = strtoupper(trim((string) ($data['code'] ?? '')));
            if ($code !== '') {
                $codes[] = $code;
            }
        }

        return $codes;
    }

    private function makeUniqueCard(int $points): string
    {
        if (! Schema::hasTable('video_cards')) {
            throw new \RuntimeException('卡密表不存在');
        }
        $points = max(0, $points);
        $now = time();
        for ($i = 0; $i < 24; $i++) {
            $code = strtoupper(Str::random(16));
            if (VideoCard::query()->where('code', $code)->exists()) {
                continue;
            }
            VideoCard::query()->create([
                'code' => $code,
                'points' => $points,
                'status' => 1,
                'used_by' => 0,
                'used_at' => 0,
                'created_at' => $now,
            ]);

            return $code;
        }

        throw new \RuntimeException('卡密生成失败');
    }
}

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

    /**
     * @param  array{type?:string,hot?:bool}  $filters
     */
    public function paginate(int $perPage = 24, array $filters = []): LengthAwarePaginator
    {
        $q = MallGood::query()->where('status', 1);
        $rawType = trim((string) ($filters['type'] ?? ''));
        if ($rawType !== '') {
            $type = self::normalizeType($rawType);
            if (in_array($type, ['goods', 'vip', 'card'], true)) {
                $q->where('type', $type);
            }
        }
        if (! empty($filters['hot']) && Schema::hasColumn('plugin_mall_goods', 'is_hot')) {
            $q->where('is_hot', 1);
        }
        if (Schema::hasColumn('plugin_mall_goods', 'is_hot')) {
            $q->orderByDesc('is_hot');
        }

        return $q->orderByDesc('sort')->orderByDesc('id')->paginate($perPage);
    }

    /** @return list<MallGood> */
    public function hotGoods(int $limit = 8): array
    {
        if (! $this->ready() || ! Schema::hasColumn('plugin_mall_goods', 'is_hot')) {
            return [];
        }

        return MallGood::query()
            ->where('status', 1)
            ->where('is_hot', 1)
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function published(int $id): ?MallGood
    {
        if (! $this->ready()) {
            return null;
        }

        return MallGood::query()->where('status', 1)->find($id);
    }

    public function memberOrders(Member $member, int $perPage = 20): LengthAwarePaginator
    {
        return MallOrder::query()
            ->where('member_id', (int) $member->id)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function memberOrder(Member $member, int $id): ?MallOrder
    {
        return MallOrder::query()
            ->where('member_id', (int) $member->id)
            ->where('id', $id)
            ->first();
    }

    public static function typeLabel(?string $type): string
    {
        return match (self::normalizeType($type)) {
            'vip' => '会员时长',
            'card' => '积分卡密',
            default => '实物周边',
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

    public static function vipDaysLabel(int $days): string
    {
        return $days < 1 ? '长期' : $days.' 天';
    }

    public function poolRemain(array $ext): int
    {
        if (! Schema::hasTable('video_cards')) {
            return 0;
        }
        $q = VideoCard::query()->where('status', 1)->where('used_by', 0);
        $want = (int) ($ext['card_points'] ?? 0);
        if ($want > 0) {
            $q->where('points', $want);
        }
        $taken = $this->takenCardCodes();
        if ($taken !== []) {
            $q->whereNotIn('code', $taken);
        }

        return (int) $q->count();
    }

    /**
     * @param  array{contact?:string,address?:string}  $extra
     * @return array<string, mixed>
     */
    public function buy(int $id, Member $member, array $extra = []): array
    {
        if (! $this->ready()) {
            return Result::fail('商城未启用');
        }

        try {
            return DB::transaction(function () use ($id, $member, $extra) {
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

                $contact = mb_substr(trim((string) ($extra['contact'] ?? '')), 0, 80);
                $address = mb_substr(trim((string) ($extra['address'] ?? '')), 0, 250);
                if ($type === 'goods' && $contact === '') {
                    return Result::fail('实物兑换请填写联系方式');
                }

                $groupId = 0;
                $vipDays = 0;
                $assignCard = null;
                $autoCredit = false;
                if ($type === 'vip') {
                    $groupId = (int) ($ext['group_id'] ?? 0);
                    $vipDays = max(0, (int) ($ext['days'] ?? $ext['vip_days'] ?? 0));
                    if ($groupId < 1 || ! Schema::hasTable('member_groups') || ! MemberGroup::query()->where('id', $groupId)->exists()) {
                        return Result::fail('会员组不存在');
                    }
                }
                if ($type === 'card') {
                    if (! Schema::hasTable('video_cards')) {
                        return Result::fail('卡密表不存在');
                    }
                    $autoCredit = (int) ($ext['auto_credit'] ?? 0) === 1;
                    $mode = strtolower(trim((string) ($ext['mode'] ?? $ext['card_mode'] ?? 'generate')));
                    if ($mode === 'assign') {
                        $assignCard = $this->lockAssignableCard($ext);
                        if (! $assignCard) {
                            return Result::fail('没有可领取的卡密');
                        }
                    }
                }

                $now = time();
                $fresh->points = (int) $fresh->points - $cost;
                $this->writePointLog($fresh, -$cost, 'mall', '兑换 '.$goods->name, $now);

                $expireAt = 0;
                if ($type === 'vip') {
                    $expireAt = $this->applyVipGroup($fresh, $groupId, $vipDays);
                }

                $status = 1;
                $completeAt = 0;
                $deliveryArr = [];
                $msg = '兑换成功，等待发货';
                $credited = 0;

                if ($type === 'vip') {
                    $status = 2;
                    $completeAt = $now;
                    $deliveryArr = [
                        'type' => 'vip',
                        'group_id' => $groupId,
                        'days' => $vipDays,
                        'expire_at' => $expireAt,
                    ];
                    $msg = $vipDays > 0
                        ? '兑换成功，会员已开通至 '.date('Y-m-d H:i', $expireAt)
                        : '兑换成功，会员组已更新（长期）';
                } elseif ($type === 'card') {
                    $cardPoints = max(0, (int) ($ext['card_points'] ?? 0));
                    if ($assignCard) {
                        $code = (string) $assignCard->code;
                        if ($cardPoints < 1) {
                            $cardPoints = (int) $assignCard->points;
                        }
                    } else {
                        if ($cardPoints < 1) {
                            $cardPoints = (int) $goods->points;
                        }
                        $code = $this->makeUniqueCard($cardPoints);
                    }

                    if ($autoCredit) {
                        $card = VideoCard::query()->where('code', $code)->lockForUpdate()->first();
                        if ($card && (int) $card->used_by === 0 && (int) $card->status === 1) {
                            $card->status = 0;
                            $card->used_by = (int) $fresh->id;
                            $card->used_at = $now;
                            $card->save();
                            $credited = (int) $card->points;
                            $fresh->points = (int) $fresh->points + $credited;
                            $this->writePointLog($fresh, $credited, 'mall_card', '商城卡密到账 '.$code, $now);
                        }
                        $msg = '兑换成功，已到账 '.$credited.' 积分';
                    } else {
                        $msg = '兑换成功，卡密 '.$code.'（可在会员中心兑换）';
                    }

                    $status = 2;
                    $completeAt = $now;
                    $deliveryArr = [
                        'type' => 'card',
                        'code' => $code,
                        'auto_credit' => $autoCredit ? 1 : 0,
                        'credited' => $credited,
                    ];
                }

                $fresh->save();
                $goods->stock = (int) $goods->stock - 1;
                if (Schema::hasColumn('plugin_mall_goods', 'sales')) {
                    $goods->sales = (int) ($goods->sales ?? 0) + 1;
                }
                $goods->save();

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
                    $orderPayload['delivery'] = json_encode($deliveryArr, JSON_UNESCAPED_UNICODE) ?: '';
                }
                if (Schema::hasColumn('plugin_mall_orders', 'complete_at')) {
                    $orderPayload['complete_at'] = $completeAt;
                }
                if (Schema::hasColumn('plugin_mall_orders', 'contact')) {
                    $orderPayload['contact'] = $contact;
                }
                if (Schema::hasColumn('plugin_mall_orders', 'address')) {
                    $orderPayload['address'] = $address;
                }
                $order = MallOrder::query()->create($orderPayload);

                return Result::success([
                    'id' => $order->id,
                    'type' => $type,
                    'status' => $status,
                    'delivery' => $deliveryArr,
                    'points' => (int) $fresh->points,
                    'orders_url' => url('/mall/orders'),
                ], $msg);
            });
        } catch (\RuntimeException $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : '兑换失败，请稍后再试');
        } catch (\Throwable) {
            return Result::fail('兑换失败，请稍后再试');
        }
    }

    /** Apply VIP group; returns expire_at unix (0 = permanent). */
    public function applyVipGroup(Member $member, int $groupId, int $days): int
    {
        $origGid = (int) $member->getOriginal('group_id');
        $origExpire = Schema::hasColumn('members', 'group_expire_at')
            ? (int) ($member->getOriginal('group_expire_at') ?? 0)
            : 0;

        $member->group_id = $groupId;
        if (! Schema::hasColumn('members', 'group_expire_at')) {
            return 0;
        }
        if ($days < 1) {
            $member->group_expire_at = 0;

            return 0;
        }

        $now = time();
        $base = $now;
        if ($origGid === $groupId && $origExpire > $now) {
            $base = $origExpire;
        }
        $expireAt = $base + ($days * 86400);
        $member->group_expire_at = $expireAt;

        return $expireAt;
    }

    private function writePointLog(Member $member, int $points, string $type, string $remark, int $now): void
    {
        if (! Schema::hasTable('member_point_logs')) {
            return;
        }
        MemberPointLog::query()->create([
            'member_id' => (int) $member->id,
            'points' => $points,
            'balance' => (int) $member->points,
            'type' => $type,
            'remark' => mb_substr($remark, 0, 250),
            'created_at' => $now,
        ]);
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
            // Skip already auto-credited codes — they are marked used_by on the card.
            $code = strtoupper(trim((string) ($data['code'] ?? '')));
            if ($code !== '' && (int) ($data['auto_credit'] ?? 0) !== 1) {
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

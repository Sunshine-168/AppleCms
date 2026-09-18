<?php

namespace Plugins\Coupon\Services;

use App\Models\Member\MemberOrder;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\Coupon\Models\PluginCoupon;
use Plugins\Coupon\Models\PluginCouponUser;
use Throwable;

class CouponService
{
    public const RESERVATION_TTL = 1800;

    /** @var list<string> */
    public const TYPES = ['amount', 'discount'];

    /** @var list<string> */
    public const SCENES = ['all', 'recharge', 'vip'];

    /** @var list<string> */
    public const LONGS = ['day', 'week', 'month', 'year'];

    public function ready(): bool
    {
        return Schema::hasTable('plugin_coupons') && Schema::hasTable('plugin_coupon_users');
    }

    /**
     * @return list<array{id:int,name:string}>
     */
    public function adminGroups(): array
    {
        if (! Schema::hasTable('member_groups')) {
            return [];
        }

        return \App\Models\Member\MemberGroup::query()
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(static fn ($row) => ['id' => (int) $row->id, 'name' => (string) $row->name])
            ->all();
    }

    /** @param  array<string, mixed>  $data */
    public function saveCampaign(array $data, ?int $id = null): array
    {
        if (! $this->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $name = mb_substr(trim((string) ($data['name'] ?? '')), 0, 100);
        if ($id === null && $name === '') {
            return Result::fail('请填写名称');
        }
        $type = (string) ($data['type'] ?? 'amount');
        if (! in_array($type, self::TYPES, true)) {
            return Result::fail('类型只接受满减或折扣');
        }
        $value = round((float) ($data['value'] ?? 0), 2);
        if ($value <= 0) {
            return Result::fail('面额或折扣必须大于 0');
        }
        if ($type === 'discount' && $value > 100) {
            return Result::fail('折扣不能超过 100');
        }
        $scene = (string) ($data['scene'] ?? 'all');
        if (! in_array($scene, self::SCENES, true)) {
            return Result::fail('场景只接受通用、充值、会员');
        }
        $total = (int) ($data['total'] ?? 0);
        if ($total < 1) {
            return Result::fail('发放总数至少 1');
        }
        $row = $id ? PluginCoupon::query()->find($id) : new PluginCoupon;
        if ($id && ! $row) {
            return Result::fail('数据不存在');
        }
        if ($id && $total < (int) $row->received) {
            return Result::fail('发放总数不能小于已领 '.$row->received);
        }
        $now = time();
        if ($id === null) {
            $row->received = 0;
            $row->used = 0;
            $row->created_at = $now;
            $row->status = 1;
        }
        if ($name !== '') {
            $row->name = $name;
        }
        $row->type = $type;
        $row->value = number_format($value, 2, '.', '');
        $row->min_price = number_format(max(0, round((float) ($data['min_price'] ?? 0), 2)), 2, '.', '');
        $row->scene = $scene;
        $row->total = $total;
        $row->per_user = 1;
        $row->target = $this->encodeTarget($data);
        $row->start_at = $this->timeValue($data['start_at'] ?? 0);
        $row->end_at = $this->timeValue($data['end_at'] ?? 0);
        if (array_key_exists('status', $data) && $data['status'] !== '' && $data['status'] !== null) {
            $row->status = (int) $data['status'] === 1 ? 1 : 0;
        }
        $row->updated_at = $now;
        $row->save();

        return Result::success(['id' => (int) $row->id], $id ? '已保存' : '已创建');
    }

    public function receive(int $couponId, int $memberId): array
    {
        if (! $this->ready() || $couponId < 1 || $memberId < 1) {
            return Result::fail('参数错误');
        }

        try {
            return DB::transaction(function () use ($couponId, $memberId) {
                /** @var PluginCoupon|null $coupon */
                $coupon = PluginCoupon::query()->lockForUpdate()->find($couponId);
                if (! $coupon) {
                    return Result::fail('优惠券不存在');
                }
                $now = time();
                $live = $this->liveError($coupon, $now);
                if ($live !== null) {
                    return Result::fail($live);
                }
                if ((int) $coupon->received >= (int) $coupon->total) {
                    return Result::fail('已经领完');
                }
                $mine = PluginCouponUser::query()
                    ->where('coupon_id', $couponId)
                    ->where('member_id', $memberId)
                    ->count();
                if ($mine >= 1) {
                    return Result::fail('每人限领 1 张');
                }
                $coupon->received = (int) $coupon->received + 1;
                $coupon->updated_at = $now;
                $coupon->save();
                $row = PluginCouponUser::query()->create([
                    'coupon_id' => $couponId,
                    'member_id' => $memberId,
                    'status' => 0,
                    'received_at' => $now,
                    'used_at' => 0,
                    'order_id' => 0,
                    'order_no' => '',
                ]);

                return Result::success(['id' => (int) $row->id], '已领取');
            });
        } catch (Throwable $e) {
            if ($this->uniqueConflict($e)) {
                return Result::fail('每人限领 1 张');
            }

            return Result::fail('领取失败');
        }
    }

    /**
     * @param  array{group_id?:int,long?:string}  $opts
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    public function quote(string $scene, float $yuan, int $couponUserId, int $memberId, array $opts = []): array
    {
        $yuan = round($yuan, 2);
        $originalFen = $this->toFen($yuan);
        if ($couponUserId < 1) {
            return Result::success([
                'original_fen' => $originalFen,
                'discount_fen' => 0,
                'pay_fen' => $originalFen,
                'coupon_user_id' => 0,
            ]);
        }
        if (! $this->ready() || $memberId < 1) {
            return Result::fail('优惠券不可用');
        }
        $cu = PluginCouponUser::query()->find($couponUserId);
        if (! $cu || (int) $cu->member_id !== $memberId) {
            return Result::fail('这张券不是你的');
        }
        $this->releaseExpired((int) $cu->id);
        $cu->refresh();
        if ((int) $cu->status === 1) {
            return Result::fail('优惠券已使用');
        }
        if ((string) $cu->order_no !== '') {
            $same = (string) ($opts['order_no'] ?? '');
            if ($same === '' || $same !== (string) $cu->order_no) {
                return Result::fail('优惠券已被其他订单占用');
            }
        }
        $coupon = PluginCoupon::query()->find((int) $cu->coupon_id);
        if (! $coupon) {
            return Result::fail('优惠券不存在');
        }
        $live = $this->liveError($coupon, time());
        if ($live !== null) {
            return Result::fail($live);
        }
        $couponScene = (string) $coupon->scene;
        if ($couponScene !== 'all' && $couponScene !== $scene) {
            return Result::fail('这张券不适用于当前场景');
        }
        if ($scene === 'vip') {
            $target = $this->decodeTarget((string) ($coupon->target ?? ''));
            $gid = (int) ($opts['group_id'] ?? 0);
            $long = trim((string) ($opts['long'] ?? ''));
            $groups = $target['groups'] ?? [];
            $longs = $target['longs'] ?? [];
            if ($groups !== [] && ! in_array($gid, $groups, true)) {
                return Result::fail('这张券不适用于该会员组');
            }
            if ($longs !== [] && ! in_array($long, $longs, true)) {
                return Result::fail('这张券不适用于此时长');
            }
        }
        $min = round((float) $coupon->min_price, 2);
        if ($yuan + 0.00001 < $min) {
            return Result::fail('未达到满减门槛');
        }
        $type = (string) $coupon->type;
        $value = (float) $coupon->value;
        if ($type === 'discount') {
            $pct = min(100, max(0, $value));
            $discountFen = (int) round($originalFen * $pct / 100);
        } else {
            $discountFen = $this->toFen($value);
        }
        $payFen = $originalFen - $discountFen;
        if ($payFen < 1) {
            return Result::fail('全额抵扣不成单，至少要付 0.01 元');
        }

        return Result::success([
            'original_fen' => $originalFen,
            'discount_fen' => $discountFen,
            'pay_fen' => $payFen,
            'coupon_user_id' => (int) $cu->id,
            'coupon_id' => (int) $coupon->id,
            'coupon_name' => (string) $coupon->name,
        ]);
    }

    public function reserve(int $couponUserId, int $memberId, int $orderId, string $orderNo): array
    {
        if (! $this->ready() || $couponUserId < 1 || $memberId < 1 || $orderId < 1 || $orderNo === '') {
            return Result::fail('参数错误');
        }
        $this->releaseExpired($couponUserId);
        $cu = PluginCouponUser::query()->find($couponUserId);
        if (! $cu || (int) $cu->member_id !== $memberId) {
            return Result::fail('这张券不是你的');
        }
        if ((int) $cu->status === 1) {
            return Result::fail('优惠券已使用');
        }
        if ((string) $cu->order_no === $orderNo) {
            return Result::success(['id' => (int) $cu->id], '已占用');
        }
        if ((string) $cu->order_no !== '') {
            return Result::fail('优惠券已被其他订单占用');
        }
        $n = PluginCouponUser::query()
            ->where('id', $couponUserId)
            ->where('status', 0)
            ->where('order_no', '')
            ->update(['order_id' => $orderId, 'order_no' => $orderNo]);
        if ($n < 1) {
            return Result::fail('优惠券已被其他订单占用');
        }

        return Result::success(['id' => (int) $cu->id], '已占用');
    }

    public function writeOff(int $couponUserId, int $memberId, int $orderId, string $orderNo): array
    {
        if (! $this->ready() || $couponUserId < 1) {
            return Result::success();
        }
        $cu = PluginCouponUser::query()->find($couponUserId);
        if (! $cu || (int) $cu->member_id !== $memberId) {
            return Result::fail('这张券不是你的');
        }
        if ((int) $cu->status === 1) {
            return Result::success(['id' => (int) $cu->id, 'idempotent' => true], '已核销');
        }
        $now = time();
        $n = PluginCouponUser::query()
            ->where('id', $couponUserId)
            ->where('status', 0)
            ->update([
                'status' => 1,
                'used_at' => $now,
                'order_id' => $orderId,
                'order_no' => $orderNo,
            ]);
        if ($n < 1) {
            return Result::fail('优惠券已使用');
        }
        PluginCoupon::query()->where('id', (int) $cu->coupon_id)->increment('used');

        return Result::success(['id' => (int) $cu->id], '已核销');
    }

    public function release(int $couponUserId): void
    {
        if (! $this->ready() || $couponUserId < 1) {
            return;
        }
        PluginCouponUser::query()
            ->where('id', $couponUserId)
            ->where('status', 0)
            ->update(['order_id' => 0, 'order_no' => '']);
    }

    public function releaseExpired(int $couponUserId): void
    {
        if (! $this->ready() || $couponUserId < 1) {
            return;
        }
        $cu = PluginCouponUser::query()->find($couponUserId);
        if (! $cu || (int) $cu->status === 1 || (string) $cu->order_no === '') {
            return;
        }
        $order = null;
        if (Schema::hasTable('member_orders')) {
            $order = MemberOrder::query()->where('order_no', (string) $cu->order_no)->first();
        }
        $stale = ! $order
            || ((int) $order->status === 0 && (int) $order->created_at < time() - self::RESERVATION_TTL)
            || (int) $order->status === 2;
        if ($stale) {
            $this->release($couponUserId);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function shopWindow(int $memberId): array
    {
        if (! $this->ready()) {
            return [];
        }
        $now = time();
        $taken = $memberId > 0
            ? PluginCouponUser::query()->where('member_id', $memberId)->pluck('coupon_id')->all()
            : [];
        $rows = PluginCoupon::query()->where('status', 1)->orderByDesc('id')->get();
        $out = [];
        foreach ($rows as $row) {
            if ($this->liveError($row, $now) !== null) {
                continue;
            }
            $left = max(0, (int) $row->total - (int) $row->received);
            $out[] = $this->present($row, [
                'left' => $left,
                'mine' => in_array((int) $row->id, array_map('intval', $taken), true),
            ]);
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function wallet(int $memberId, string $scene = '', float $yuan = 0): array
    {
        if (! $this->ready() || $memberId < 1) {
            return [];
        }
        $ids = PluginCouponUser::query()
            ->where('member_id', $memberId)
            ->orderByDesc('id')
            ->get();
        $out = [];
        foreach ($ids as $cu) {
            $this->releaseExpired((int) $cu->id);
            $cu->refresh();
            $coupon = PluginCoupon::query()->find((int) $cu->coupon_id);
            if (! $coupon) {
                continue;
            }
            $row = $this->present($coupon, [
                'coupon_user_id' => (int) $cu->id,
                'ticket_status' => (int) $cu->status,
                'order_no' => (string) $cu->order_no,
                'received_at' => (int) $cu->received_at,
                'used_at' => (int) $cu->used_at,
            ]);
            if ($scene !== '') {
                $couponScene = (string) $coupon->scene;
                if ($couponScene !== 'all' && $couponScene !== $scene) {
                    continue;
                }
                if ((int) $cu->status === 1) {
                    continue;
                }
                if ($yuan > 0 && $yuan + 0.00001 < (float) $coupon->min_price) {
                    continue;
                }
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public function present(PluginCoupon $row, array $extra = []): array
    {
        $target = $this->decodeTarget((string) ($row->target ?? ''));

        return array_merge([
            'id' => (int) $row->id,
            'name' => (string) $row->name,
            'type' => (string) $row->type,
            'type_label' => (string) $row->type === 'discount' ? '折扣' : '满减',
            'value' => (string) $row->value,
            'min_price' => (string) $row->min_price,
            'scene' => (string) $row->scene,
            'scene_label' => match ((string) $row->scene) {
                'recharge' => '充值',
                'vip' => '会员',
                default => '通用',
            },
            'total' => (int) $row->total,
            'received' => (int) $row->received,
            'used' => (int) $row->used,
            'per_user' => 1,
            'status' => (int) $row->status,
            'status_label' => (int) $row->status === 1 ? '启用' : '停用',
            'start_at' => (int) $row->start_at,
            'end_at' => (int) $row->end_at,
            'end_label' => (int) $row->end_at < 1 ? '长期' : date('Y-m-d H:i', (int) $row->end_at),
            'group_ids' => $target['groups'] ?? [],
            'longs' => $target['longs'] ?? [],
            'target_label' => $this->targetLabel($target),
        ], $extra);
    }

    /** @param  array<string, mixed>  $data */
    private function encodeTarget(array $data): string
    {
        $groups = $data['group_ids'] ?? [];
        if (is_string($groups)) {
            $groups = preg_split('/[,\s]+/', $groups) ?: [];
        }
        $longs = $data['longs'] ?? [];
        if (is_string($longs)) {
            $longs = preg_split('/[,\s]+/', $longs) ?: [];
        }
        $target = [];
        $gids = [];
        foreach ((array) $groups as $gid) {
            $n = (int) $gid;
            if ($n > 0) {
                $gids[] = $n;
            }
        }
        if ($gids !== []) {
            $target['groups'] = array_values(array_unique($gids));
        }
        $keep = [];
        foreach ((array) $longs as $long) {
            $long = strtolower(trim((string) $long));
            if (in_array($long, self::LONGS, true)) {
                $keep[] = $long;
            }
        }
        if ($keep !== []) {
            $target['longs'] = array_values(array_unique($keep));
        }

        return $target === [] ? '' : (string) json_encode($target, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array{groups?:list<int>,longs?:list<string>}
     */
    private function decodeTarget(string $raw): array
    {
        if ($raw === '') {
            return [];
        }
        $arr = json_decode($raw, true);
        if (! is_array($arr)) {
            return [];
        }
        $groups = [];
        foreach ((array) ($arr['groups'] ?? []) as $gid) {
            $n = (int) $gid;
            if ($n > 0) {
                $groups[] = $n;
            }
        }
        $longs = [];
        foreach ((array) ($arr['longs'] ?? []) as $long) {
            $long = strtolower(trim((string) $long));
            if (in_array($long, self::LONGS, true)) {
                $longs[] = $long;
            }
        }
        $out = [];
        if ($groups !== []) {
            $out['groups'] = array_values(array_unique($groups));
        }
        if ($longs !== []) {
            $out['longs'] = array_values(array_unique($longs));
        }

        return $out;
    }

    /** @param  array{groups?:list<int>,longs?:list<string>}  $target */
    private function targetLabel(array $target): string
    {
        $bits = [];
        if (($target['groups'] ?? []) !== []) {
            $bits[] = '限会员组';
        }
        if (($target['longs'] ?? []) !== []) {
            $map = ['day' => '日', 'week' => '周', 'month' => '月', 'year' => '年'];
            $bits[] = implode('/', array_map(static fn ($k) => $map[$k] ?? $k, $target['longs']));
        }

        return $bits === [] ? '不限' : implode(' · ', $bits);
    }

    private function liveError(PluginCoupon $coupon, int $now): ?string
    {
        if ((int) $coupon->status !== 1) {
            return '优惠券已停用';
        }
        if ((int) $coupon->start_at > 0 && $now < (int) $coupon->start_at) {
            return '优惠券未开始';
        }
        if ((int) $coupon->end_at > 0 && $now > (int) $coupon->end_at) {
            return '优惠券已过期';
        }

        return null;
    }

    private function timeValue(mixed $raw): int
    {
        if (is_numeric($raw)) {
            return max(0, (int) $raw);
        }
        $s = trim((string) $raw);
        if ($s === '') {
            return 0;
        }
        $ts = strtotime(str_replace('T', ' ', $s));

        return $ts === false ? 0 : $ts;
    }

    private function toFen(float $yuan): int
    {
        return (int) round($yuan * 100);
    }

    private function uniqueConflict(Throwable $e): bool
    {
        $msg = $e->getMessage();

        return str_contains($msg, 'UNIQUE') || str_contains($msg, 'unique') || str_contains($msg, 'Duplicate');
    }
}

<?php

namespace App\Services\Video;

use App\Models\Member\Member;
use App\Models\Member\MemberOrder;
use App\Models\Member\MemberPointLog;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MemberOrderService
{
    public function settle(int $orderId, string $tradeNo = '', string $channel = ''): array
    {
        if ($orderId < 1) {
            return Result::fail('订单不存在');
        }

        return DB::transaction(function () use ($orderId, $tradeNo, $channel) {
            /** @var MemberOrder|null $row */
            $row = MemberOrder::query()->lockForUpdate()->find($orderId);
            if (! $row) {
                return Result::fail('订单不存在');
            }
            $status = (int) $row->status;
            if ($status === 1) {
                return Result::success(['id' => $row->id, 'idempotent' => true], '已到账');
            }
            if ($status !== 0) {
                return Result::fail('订单已关闭');
            }
            $row->status = 1;
            if ($tradeNo !== '' && Schema::hasColumn($row->getTable(), 'trade_no')) {
                $row->trade_no = mb_substr($tradeNo, 0, 64);
            }
            if ($channel !== '' && Schema::hasColumn($row->getTable(), 'channel')) {
                $row->channel = mb_substr($channel, 0, 20);
            }
            $row->updated_at = time();
            $row->save();
            $this->creditRow($row);
            if ((int) ($row->coupon_user_id ?? 0) > 0 && class_exists(\Plugins\Coupon\Services\CouponService::class)) {
                $written = app(\Plugins\Coupon\Services\CouponService::class)->writeOff(
                    (int) $row->coupon_user_id,
                    (int) $row->member_id,
                    (int) $row->id,
                    (string) $row->order_no
                );
                if ((int) ($written['code'] ?? 1) !== 0) {
                    throw new \RuntimeException((string) ($written['msg'] ?? '优惠券核销失败'));
                }
            }

            return Result::success(['id' => $row->id], '已到账');
        });
    }

    public function credit(int $orderId): void
    {
        $row = MemberOrder::query()->find($orderId);
        if ($row) {
            $this->creditRow($row);
        }
    }

    private function creditRow(MemberOrder $row): void
    {
        $memberId = (int) $row->member_id;
        $points = (int) $row->points;
        if ($memberId < 1 || $points < 1) {
            return;
        }
        $remark = '订单 '.$row->order_no;
        if (Schema::hasTable('member_point_logs')) {
            $exists = MemberPointLog::query()
                ->where('member_id', $memberId)
                ->where('type', 'order')
                ->where('remark', $remark)
                ->exists();
            if ($exists) {
                return;
            }
        }
        $member = Member::query()->find($memberId);
        if (! $member) {
            return;
        }
        $member->points = max(0, (int) $member->points + $points);
        $member->save();
        if (! Schema::hasTable('member_point_logs')) {
            return;
        }
        MemberPointLog::query()->create([
            'member_id' => $memberId,
            'points' => $points,
            'balance' => (int) $member->points,
            'type' => 'order',
            'remark' => mb_substr($remark, 0, 250),
            'created_at' => time(),
        ]);
    }
}

<?php

namespace App\Services\Video;

use App\Events\MemberOrderPaid;
use App\Models\Member\Member;
use App\Models\Member\MemberOrder;
use App\Models\Member\MemberPm;
use App\Models\Member\MemberPointLog;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MemberOrderService
{
    /**
     * Payment callback / gateway success path.
     * Marks pending → paid, then runs system fulfillment (idempotent).
     */
    public function settle(int $orderId, string $tradeNo = '', string $channel = ''): array
    {
        if ($orderId < 1) {
            return Result::fail('订单不存在');
        }

        try {
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
                if (Schema::hasColumn($row->getTable(), 'paid_at')) {
                    $row->paid_at = time();
                }
                $row->updated_at = time();
                $row->save();

                $this->fulfill($row, true);

                return Result::success(['id' => $row->id, 'points' => (int) $row->points], '已到账');
            });
        } catch (\Throwable $e) {
            Log::warning('member_order.settle_fail', [
                'order_id' => $orderId,
                'msg' => $e->getMessage(),
            ]);

            return Result::fail($e->getMessage() !== '' ? $e->getMessage() : '入账失败');
        }
    }

    /**
     * Admin「确认已付」等：订单已是已付时补跑入账（积分幂等）。
     */
    public function fulfillById(int $orderId): void
    {
        $row = MemberOrder::query()->find($orderId);
        if ($row && (int) $row->status === 1) {
            $this->fulfill($row, false);
        }
    }

    /** @deprecated Prefer settle() or fulfillById() */
    public function credit(int $orderId): void
    {
        $this->fulfillById($orderId);
    }

    /**
     * System integration after paid:
     * points + plog → coupon write-off → activity → inbox → event.
     */
    public function fulfill(MemberOrder $row, bool $fromPayment = false): void
    {
        $this->creditRow($row);
        $this->writeOffCoupon($row);
        $this->reportActivity($row);
        $this->notifyMember($row);
        try {
            event(new MemberOrderPaid($row->fresh() ?? $row, $fromPayment));
        } catch (\Throwable $e) {
            Log::warning('member_order.event_fail', ['order_id' => $row->id, 'msg' => $e->getMessage()]);
        }
        Log::info('member_order.paid', [
            'order_id' => (int) $row->id,
            'order_no' => (string) $row->order_no,
            'member_id' => (int) $row->member_id,
            'points' => (int) $row->points,
            'amount' => (int) $row->amount,
            'channel' => (string) ($row->channel ?? ''),
            'from_payment' => $fromPayment,
        ]);
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

    private function writeOffCoupon(MemberOrder $row): void
    {
        if ((int) ($row->coupon_user_id ?? 0) < 1) {
            return;
        }
        if (! class_exists(\Plugins\Coupon\Services\CouponService::class)) {
            return;
        }
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

    private function reportActivity(MemberOrder $row): void
    {
        try {
            if (! class_exists(\App\Services\Member\MemberActivityService::class)) {
                return;
            }
            $activity = app(\App\Services\Member\MemberActivityService::class);
            if (! $activity->ready()) {
                return;
            }
            $member = Member::query()->find((int) $row->member_id);
            if (! $member) {
                return;
            }
            $activity->reportRecharge($member);
        } catch (\Throwable $e) {
            Log::warning('member_order.activity_fail', ['order_id' => $row->id, 'msg' => $e->getMessage()]);
        }
    }

    private function notifyMember(MemberOrder $row): void
    {
        if (! Schema::hasTable('member_pms')) {
            return;
        }
        $to = (int) $row->member_id;
        if ($to < 1) {
            return;
        }
        $title = '充值到账';
        $yuan = number_format(((int) $row->amount) / 100, 2, '.', '');
        $content = '订单 '.$row->order_no.' 已支付成功，到账 '.(int) $row->points.' 积分（实付 '.$yuan.' 元）。';
        // Avoid duplicate PM on retry.
        $exists = MemberPm::query()
            ->where('to_id', $to)
            ->where('from_id', 0)
            ->where('title', $title)
            ->where('content', $content)
            ->exists();
        if ($exists) {
            return;
        }
        try {
            MemberPm::query()->create([
                'from_id' => 0,
                'to_id' => $to,
                'title' => $title,
                'content' => mb_substr($content, 0, 500),
                'is_read' => 0,
                'created_at' => time(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('member_order.pm_fail', ['order_id' => $row->id, 'msg' => $e->getMessage()]);
        }
    }
}

<?php

namespace Plugins\Mall\Services;

use App\Models\Member\Member;
use App\Models\Member\MemberPointLog;
use App\Support\Utils\Result;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\Mall\Models\MallGood;
use Plugins\Mall\Models\MallOrder;

class MallService
{
    public function ready(): bool
    {
        return Schema::hasTable('plugin_mall_goods') && Schema::hasTable('plugin_mall_orders');
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
                $fresh->points = (int) $fresh->points - $cost;
                $fresh->save();
                $goods->stock = (int) $goods->stock - 1;
                $goods->save();
                $order = MallOrder::query()->create([
                    'member_id' => (int) $fresh->id,
                    'goods_id' => (int) $goods->id,
                    'goods_name' => (string) $goods->name,
                    'points' => $cost,
                    'status' => 1,
                    'remark' => '',
                    'created_at' => time(),
                ]);
                if (Schema::hasTable('member_point_logs')) {
                    MemberPointLog::query()->create([
                        'member_id' => (int) $fresh->id,
                        'points' => -$cost,
                        'balance' => (int) $fresh->points,
                        'type' => 'mall',
                        'remark' => '兑换 '.$goods->name,
                        'created_at' => time(),
                    ]);
                }

                return Result::success(['id' => $order->id], '兑换成功，等待发货');
            });
        } catch (\Throwable) {
            return Result::fail('兑换失败，请稍后再试');
        }
    }
}

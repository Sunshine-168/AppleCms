<?php

namespace Plugins\Pay\Services;

use App\Models\Member\MemberOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayStatsService
{
    /** @return array<string, mixed> */
    public function summary(): array
    {
        if (! Schema::hasTable('member_orders')) {
            return $this->empty();
        }

        $todayStart = strtotime('today');
        $yesterdayStart = strtotime('yesterday');
        $weekStart = strtotime('-6 days', $todayStart);
        $monthStart = strtotime('-29 days', $todayStart);

        return [
            'pending' => $this->countStatus(0),
            'paid' => $this->countStatus(1),
            'closed' => $this->countStatus(2),
            'today' => $this->window($todayStart, null),
            'yesterday' => $this->window($yesterdayStart, $todayStart),
            'week' => $this->window($weekStart, null),
            'month' => $this->window($monthStart, null),
            'by_channel' => $this->byChannel($monthStart),
            'daily' => $this->dailyTrend(14),
        ];
    }

    /** @return array<string, mixed> */
    private function empty(): array
    {
        $zero = ['orders' => 0, 'amount_fen' => 0, 'amount_yuan' => '0.00', 'points' => 0];

        return [
            'pending' => 0,
            'paid' => 0,
            'closed' => 0,
            'today' => $zero,
            'yesterday' => $zero,
            'week' => $zero,
            'month' => $zero,
            'by_channel' => [],
            'daily' => [],
        ];
    }

    private function countStatus(int $status): int
    {
        return (int) MemberOrder::query()->where('status', $status)->count();
    }

    /**
     * @return array{orders:int,amount_fen:int,amount_yuan:string,points:int}
     */
    private function window(int $from, ?int $to): array
    {
        $q = MemberOrder::query()->where('status', 1);
        $timeCol = Schema::hasColumn('member_orders', 'paid_at') ? 'paid_at' : 'updated_at';
        // Prefer paid_at when set; fallback created_at for legacy rows without paid_at.
        if (Schema::hasColumn('member_orders', 'paid_at')) {
            $q->where(function ($inner) use ($from, $to) {
                $inner->where(function ($a) use ($from, $to) {
                    $a->where('paid_at', '>', 0)->where('paid_at', '>=', $from);
                    if ($to !== null) {
                        $a->where('paid_at', '<', $to);
                    }
                })->orWhere(function ($b) use ($from, $to) {
                    $b->where(function ($c) {
                        $c->where('paid_at', 0)->orWhereNull('paid_at');
                    })->where('updated_at', '>=', $from);
                    if ($to !== null) {
                        $b->where('updated_at', '<', $to);
                    }
                });
            });
        } else {
            $q->where($timeCol, '>=', $from);
            if ($to !== null) {
                $q->where($timeCol, '<', $to);
            }
        }

        $orders = (int) (clone $q)->count();
        $amount = (int) (clone $q)->sum('amount');
        $points = (int) (clone $q)->sum('points');

        return [
            'orders' => $orders,
            'amount_fen' => $amount,
            'amount_yuan' => number_format($amount / 100, 2, '.', ''),
            'points' => $points,
        ];
    }

    /** @return list<array{channel:string,label:string,orders:int,amount_yuan:string,points:int}> */
    private function byChannel(int $from): array
    {
        $labels = [
            'wechat' => '微信',
            'alipay' => '支付宝',
            'epay' => '易支付',
            'dfpay' => 'DfPay',
            'manual' => '人工',
        ];
        $rows = MemberOrder::query()
            ->select('channel', DB::raw('COUNT(*) as c'), DB::raw('SUM(amount) as amt'), DB::raw('SUM(points) as pts'))
            ->where('status', 1)
            ->where(function ($q) use ($from) {
                if (Schema::hasColumn('member_orders', 'paid_at')) {
                    $q->where(function ($a) use ($from) {
                        $a->where('paid_at', '>', 0)->where('paid_at', '>=', $from);
                    })->orWhere(function ($b) use ($from) {
                        $b->where(function ($c) {
                            $c->where('paid_at', 0)->orWhereNull('paid_at');
                        })->where('updated_at', '>=', $from);
                    });
                } else {
                    $q->where('updated_at', '>=', $from);
                }
            })
            ->groupBy('channel')
            ->orderByDesc('amt')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $ch = trim((string) ($row->channel ?? ''));
            $amt = (int) ($row->amt ?? 0);
            $out[] = [
                'channel' => $ch !== '' ? $ch : 'manual',
                'label' => $labels[$ch] ?? ($ch !== '' ? $ch : '人工'),
                'orders' => (int) ($row->c ?? 0),
                'amount_yuan' => number_format($amt / 100, 2, '.', ''),
                'points' => (int) ($row->pts ?? 0),
            ];
        }

        return $out;
    }

    /** @return list<array{day:string,orders:int,amount_yuan:string}> */
    private function dailyTrend(int $days): array
    {
        $days = max(1, min(60, $days));
        $start = strtotime('today') - (($days - 1) * 86400);
        $map = [];
        for ($i = 0; $i < $days; $i++) {
            $day = date('Y-m-d', $start + $i * 86400);
            $map[$day] = ['day' => $day, 'orders' => 0, 'amount_fen' => 0];
        }

        $q = MemberOrder::query()->where('status', 1);
        if (Schema::hasColumn('member_orders', 'paid_at')) {
            $q->where(function ($inner) use ($start) {
                $inner->where(function ($a) use ($start) {
                    $a->where('paid_at', '>', 0)->where('paid_at', '>=', $start);
                })->orWhere(function ($b) use ($start) {
                    $b->where(function ($c) {
                        $c->where('paid_at', 0)->orWhereNull('paid_at');
                    })->where('updated_at', '>=', $start);
                });
            });
        } else {
            $q->where('updated_at', '>=', $start);
        }

        foreach ($q->get(['amount', 'paid_at', 'updated_at']) as $row) {
            $ts = (int) ($row->paid_at ?? 0);
            if ($ts < 1) {
                $ts = (int) ($row->updated_at ?? 0);
            }
            if ($ts < $start) {
                continue;
            }
            $day = date('Y-m-d', $ts);
            if (! isset($map[$day])) {
                continue;
            }
            $map[$day]['orders']++;
            $map[$day]['amount_fen'] += (int) $row->amount;
        }

        $out = [];
        foreach ($map as $row) {
            $out[] = [
                'day' => $row['day'],
                'orders' => $row['orders'],
                'amount_yuan' => number_format($row['amount_fen'] / 100, 2, '.', ''),
            ];
        }

        return $out;
    }
}

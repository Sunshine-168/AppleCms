<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $table = 'mac_order';
    protected $primaryKey = 'order_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function notify(string $orderCode, string $payType): array
    {
        if ($orderCode === '' || $payType === '') {
            return ['code' => 1001, 'msg' => __('param_err')];
        }

        /** @var self|null $order */
        $order = self::query()->where('order_code', $orderCode)->first();
        if (!$order) {
            return ['code' => 1002, 'msg' => __('obtain_err')];
        }

        if ((int) $order->order_status === 1) {
            return ['code' => 1, 'msg' => __('model/order/pay_over')];
        }

        /** @var User|null $user */
        $user = User::query()->where('user_id', $order->user_id)->first();
        if (!$user) {
            return ['code' => 1003, 'msg' => __('obtain_err')];
        }

        try {
            DB::transaction(function () use ($order, $user, $payType) {
                $order->forceFill([
                    'order_status' => 1,
                    'order_pay_time' => time(),
                    'order_pay_type' => $payType,
                ])->save();

                $user->increment('user_points', (int) $order->order_points);

                Plog::query()->create([
                    'user_id' => $user->user_id,
                    'plog_type' => 1,
                    'plog_points' => (int) $order->order_points,
                    'plog_time' => time(),
                    'plog_remarks' => __('model/order/pay_ok'),
                ]);
            });
        } catch (\Throwable $e) {
            return ['code' => 2002, 'msg' => $e->getMessage() ?: __('model/order/update_status_err')];
        }

        return ['code' => 1, 'msg' => __('model/order/pay_ok')];
    }
}

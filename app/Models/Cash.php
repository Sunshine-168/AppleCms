<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cash extends Model
{
    protected $table = 'mac_cash';
    protected $primaryKey = 'cash_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function deleteWithRestore(): array
    {
        try {
            DB::transaction(function () {
                if ((int) $this->cash_status === 0) {
                    User::query()
                        ->where('user_id', $this->user_id)
                        ->update([
                            'user_points' => DB::raw('user_points + ' . (int) $this->cash_points),
                            'user_points_froze' => DB::raw('user_points_froze - ' . (int) $this->cash_points),
                        ]);
                }

                $this->delete();
            });
        } catch (\Throwable $e) {
            return ['code' => 1001, 'msg' => $e->getMessage() ?: __('del_err')];
        }

        return ['code' => 1, 'msg' => __('del_ok')];
    }

    public function auditRecord(): array
    {
        if ((int) $this->cash_status === 1) {
            return ['code' => 1, 'msg' => __('reviewed')];
        }

        try {
            DB::transaction(function () {
                $this->forceFill([
                    'cash_status' => 1,
                    'cash_time_audit' => time(),
                ])->save();

                User::query()
                    ->where('user_id', $this->user_id)
                    ->update([
                        'user_points_froze' => DB::raw('user_points_froze - ' . (int) $this->cash_points),
                    ]);

                Plog::query()->create([
                    'user_id' => $this->user_id,
                    'plog_type' => 9,
                    'plog_points' => (int) $this->cash_points,
                    'plog_time' => time(),
                    'plog_remarks' => __('admin/plog/points_withdrawal'),
                ]);
            });
        } catch (\Throwable $e) {
            return ['code' => 1001, 'msg' => $e->getMessage() ?: __('save_err')];
        }

        return ['code' => 1, 'msg' => __('audit') . __('save_ok')];
    }
}

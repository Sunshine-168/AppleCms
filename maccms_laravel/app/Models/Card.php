<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $table = 'mac_card';
    protected $primaryKey = 'card_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public static function generateBatch(int $num, int $money, int $points, string $roleNo = '', string $rolePwd = ''): void
    {
        $timestamp = time();
        $data = [];

        for ($i = 1; $i <= $num; $i++) {
            $cardNo = mac_get_rndstr(16, $roleNo);
            $cardPwd = mac_get_rndstr(8, $rolePwd);

            $data[$cardNo] = [
                'card_no' => $cardNo,
                'card_pwd' => $cardPwd,
                'card_money' => $money,
                'card_points' => $points,
                'card_add_time' => $timestamp,
            ];
        }

        static::query()->insert(array_values($data));
    }
}

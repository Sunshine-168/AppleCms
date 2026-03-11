<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'mac_group';
    protected $primaryKey = 'group_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'group_popedom' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'group_id', 'group_id');
    }
}

<?php

namespace App\Models\Video;

use App\Models\Concerns\QueryCacheTrait;
use App\Models\Concerns\QueryTrait;
use Illuminate\Database\Eloquent\Model;

class VideoStatModel extends Model
{
    protected $table = 'video_stats';
    protected $primaryKey = 'video_id';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;
}


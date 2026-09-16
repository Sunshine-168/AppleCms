<?php

namespace App\Models\Video;

use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
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


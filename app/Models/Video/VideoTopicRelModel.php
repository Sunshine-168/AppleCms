<?php

namespace App\Models\Video;

use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;

class VideoTopicRelModel extends Model
{
    protected $table = 'video_topic_rel';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;
}

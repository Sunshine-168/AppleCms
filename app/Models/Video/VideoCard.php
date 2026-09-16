<?php

namespace App\Models\Video;

use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;

class VideoCard extends Model
{
    protected $table = 'video_cards';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait;
}

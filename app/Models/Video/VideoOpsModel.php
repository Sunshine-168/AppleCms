<?php

namespace App\Models\Video;

use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;

abstract class VideoOpsModel extends Model
{
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;
}

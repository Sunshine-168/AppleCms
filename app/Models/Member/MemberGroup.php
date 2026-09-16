<?php
namespace App\Models\Member;
use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;
class MemberGroup extends Model {
    protected $table = 'member_groups';
    public $timestamps = false;
    protected $guarded = [];
    use QueryTrait, QueryCacheTrait;
}

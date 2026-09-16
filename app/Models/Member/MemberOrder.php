<?php
namespace App\Models\Member;
use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;
class MemberOrder extends Model {
    protected $table = 'member_orders';
    public $timestamps = false;
    protected $guarded = [];
    use QueryTrait, QueryCacheTrait;
}

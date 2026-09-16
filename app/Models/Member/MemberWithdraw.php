<?php
namespace App\Models\Member;
use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;
class MemberWithdraw extends Model {
    protected $table = 'member_withdraws';
    public $timestamps = false;
    protected $guarded = [];
    use QueryTrait, QueryCacheTrait;
}

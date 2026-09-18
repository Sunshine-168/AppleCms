<?php

namespace Plugins\FriendLink\Models;

use Illuminate\Database\Eloquent\Model;

class FriendLinkHit extends Model
{
    protected $table = 'plugin_friend_link_hits';

    public $timestamps = false;

    protected $guarded = [];
}

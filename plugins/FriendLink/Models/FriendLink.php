<?php

namespace Plugins\FriendLink\Models;

use Illuminate\Database\Eloquent\Model;

class FriendLink extends Model
{
    protected $table = 'plugin_friend_links';

    public $timestamps = false;

    protected $guarded = [];
}

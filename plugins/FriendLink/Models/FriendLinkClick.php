<?php

namespace Plugins\FriendLink\Models;

use Illuminate\Database\Eloquent\Model;

class FriendLinkClick extends Model
{
    protected $table = 'plugin_friend_link_clicks';

    public $timestamps = false;

    protected $guarded = [];
}

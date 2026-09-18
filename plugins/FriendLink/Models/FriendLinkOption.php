<?php

namespace Plugins\FriendLink\Models;

use Illuminate\Database\Eloquent\Model;

class FriendLinkOption extends Model
{
    protected $table = 'plugin_friend_link_options';

    public $timestamps = false;

    protected $guarded = [];
}

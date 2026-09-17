<?php

namespace Plugins\Chatroom\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = 'plugin_chat_messages';

    public $timestamps = false;

    protected $guarded = [];
}
